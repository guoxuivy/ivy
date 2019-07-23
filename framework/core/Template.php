<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class Template{
    // 引擎配置
    protected $config = [
        'token'         => false, // 自动添加表单令牌
        'token_item'    => '{__TOKEN__}', // 表单令牌替换变量
        'cache_time'    => -1, // 模板缓存时间 秒 -1永久有效
    ];

	/**
	 * 模版处理类
	 * @var string
	 */
	static $view_name = "views";        //模板文件夹名
	protected $data = array();          //仅存放assign的变量
	protected $controller = NULL;

	public function __construct(&$controller){
	    $this->config = array_merge($this->config,\Ivy::app()->C('template'));
		$this->controller = $controller;
		$this->init();
	}

	public function init(){}


    /**
     * 返回渲染后的模板
     * @param string $template
     * @param array $data
     * @param string $ext
     * @return string
     * @throws CException
     */
    public function render($template='',$data=array(),$ext='.phtml'){
        $this->data = array_merge($this->data,$data);
        return $this->display($template,$ext,true);
    }

    /**
     * 渲染输出模板
     * @param string $template
     * @param string $ext
     * @param bool $render
     * @return string
     * @throws CException
     */
    public function display($template='',$ext = '.phtml',$render = false){
        $template_path = $this->getViewFile($template,$ext);
        $cacheFile = $this->checkAndBuildTemplateCache($template_path,$ext);
        $output = $this->ob($cacheFile);
        if ($render){
            return $output;
        }
        echo $output;
    }

    /**
     * 获取模板内容,自动合并父模板内容
     * @param string $template 兼容绝对路径
     * @param string $ext
     * @return bool|mixed|string
     * @throws CException
     */
    protected function getContent($template='',$ext = '.phtml'){
        if(false===strpos($template,__PROTECTED__)){
            $template_path = $this->getViewFile($template,$ext);
        }else{
            $template_path = $template;
        }
        $content = file_get_contents($template_path);
        $regex =   '/\{extend name=[\'|"](.*?)[\'|"][^\}]*}/is';
        preg_match($regex,$content,$matches);
        $parent_template = $matches[1];
        if($parent_template){
            $parent_content = $this->getContent($parent_template,$ext);
            //block替换
            $content = $this->blockReplace($parent_content,$content);
        }
        return $content;
    }


    /**
     * 模板文件加载到ob
     * @param $cacheFile
     * @return string
     */
    public function ob($cacheFile){
        extract($this->data,EXTR_OVERWRITE);
        ob_start();
        include $cacheFile;
        $output = ob_get_clean();
        //表单token
        $this->tagToken($output);
        return $output;
    }

    /**
     * 引入其他模版文件
     * @param $template
     * @param string $ext
     * @throws CException
     */
    public function import($template,$ext = '.phtml'){
        $template_path = $this->getViewFile($template,$ext);
        $cacheFile = $this->checkAndBuildTemplateCache($template_path,$ext);
        include $cacheFile;
    }

    /**
     * 检查并生成模板缓存文件
     * @param $template_path 模板文件绝对路径
     * @param string $ext
     * @throws CException
     * @return string
     */
    public function checkAndBuildTemplateCache($template_path,$ext='.phtml'){
        $cacheFile = __RUNTIME__.DS.'template'.DS.md5($template_path).$ext;
        if (!$this->checkCache($cacheFile)) {
            $content = $this->getContent($template_path,$ext);
            $this->tagsCompiler($content);
            $this->writeCache($cacheFile, $content);
        }
        return $cacheFile;
    }

    /* 测试用的
$content = <<<EOT
{block name='content'}我是子content的
内<?php shenmhgui  ?>容{/block}This should print a capital
EOT;
$layout_content = <<<EOT
3243{block name='content'}dsf ewr123<?php dfa>
<?php fsadfsd ?>
asdf{/block}is {block name="title"}dsf ewr123<?php dfa><?php fsadfsd ?>asdf{/block}
This should print a capital
EOT;
    */
    /**
     * 布局文件block替换, block不支持嵌套
     * 子模板没有定义block时 默认整体定义为content块
     * @param $layout_content
     * @param $content
     * @return mixed
     */
    protected function blockReplace($layout_content,$content){
        $regex =   '/\{block name=[\'|"](.*?)[\'|"]\}.*?\{\/block\}/is';
        preg_match_all($regex, $content, $child_array,PREG_SET_ORDER );
        $block_child = [];
        if(empty($child_array)){
            $block_child['content'] = $content;
        }else{
            foreach ($child_array as $block){
                $name = $block[1];
                $content = $block[0];
                $block_child[$name] = $content;
            }
        }
        preg_match_all($regex, $layout_content, $pat_array,PREG_SET_ORDER);
        foreach ($pat_array as $block){
            $name = $block[1];
            $content = $block[0];
            if($block_child[$name]){
                $layout_content = str_replace($content, $block_child[$name], $layout_content);
            }
        }
        return $layout_content;
    }

    /**
     * 模板文件寻址
     * /开头为绝对路径寻址 其它为相对路径寻址
     * @param $template
     * @param string $ext
     * @return string
     * @throws CException
     */
	public function getViewFile($template,$ext = '.phtml'){
		$template=rtrim($template);
		$r = $this->controller->getRouter();
		if($template===''){
			$template=$r['action'];
		}
		$template_arr = explode("/",$template);
		if($template_arr[0]==null){
			//绝对路径查找
			$template_arr = array_filter($template_arr);
			if(2===count($template_arr)){
				$template=implode(DS, $template_arr);
				$template_path=__PROTECTED__.DS.self::$view_name.DS.$template.$ext;
				if(!file_exists($template_path)){
					throw new CException('模版-'.$template.'-不存在!');
				}
			} elseif (3===count($template_arr)){
				$module=array_shift($template_arr);
				$template=implode(DS, $template_arr);
				$template_path=__PROTECTED__.DS."modules".DS.$module.DS.self::$view_name.DS.$template.$ext;
				if(!file_exists($template_path)){
					throw new CException('模版-'.$template.'-不存在!');
				}
			}else{
                throw new CException('模版-参数-错误!');
            }
			return $template_path;
		}else{
			//相对路径查找
			$template=implode(DS, $template_arr);
			if(3===count($template_arr)){
				$module=array_shift($template_arr);
				$template=implode(DS, $template_arr);
				$template_path=__PROTECTED__.DS."modules".DS.$module.DS.self::$view_name.DS.$template.$ext;
				if(!file_exists($template_path)){
					throw new CException('模版-'.$template.'-不存在!');
				}
			}else{
				if(1==count($template_arr)) $template=$r['controller'].DS.$template_arr[0];
				if(isset($r['module']) && !empty($r['module'])){
					$template_path=__PROTECTED__.DS."modules".DS.$r['module'].DS.self::$view_name.DS.$template.$ext;
					if(!file_exists($template_path)){
						throw new CException('模版-'.$template.'-不存在!');
					}
				}else{
					$template_path=__PROTECTED__.DS.self::$view_name.DS.$template.$ext;
					if(!file_exists($template_path)){
						throw new CException('模版-'.$template.'-不存在!');
					}
				}
			}
			return $template_path;
		}
	}

    /**
     * 格式化url
     * @param string $uri   admin/order/index
     * @param array $param  array("id"=>1)
     * @return mixed
     */
	public function url($uri="",$param=array()){
		return $this->controller->url($uri,$param);
	}

    /**
     * 模版变量传递
     * @param string $key
     * @param string $value
     * @return $this
     */
	public function assign($key='',$value=''){
		if($key&&$value&&is_string($key)){
			$this->data[$key] = $value;
		}
		if($key&&is_array($key)){
			foreach($key as $k=>$v){
				$this->data[$k] = $v;
			}
		}
		return $this;
	}


    /**
     * css+js+img 路径
     * @param $name
     * @return string
     */
	public function basePath($name){
		return '/'.$name;
	}


    /**
     * 设置属性，供模版使用
     * @param $k
     * @param $v
     */
	public function __set($k,$v){
		$this->$k = $v;
	}


    /**
     * token注入
     * @param $content
     */
	public function tagToken(&$content){
        $token_item = $this->config['token_item'];
		if($this->config['token']) {
			if(strpos($content,$token_item)) {
                // 指定表单令牌隐藏域位置
                $content = str_replace($token_item,$this->buildToken(),$content);
            }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {
                // 智能生成表单令牌隐藏域
                $content = str_replace($match[0],$this->buildToken().$match[0],$content);
            }
		}else{
			$content = str_replace($token_item,'',$content);
		}
	}

    /**
     * 创建表单令牌
     * @return string
     */
	private function buildToken() {
		$tokenName  = '__hash__';
		$tokenType  = 'md5';
		$tokenArr   = \Ivy::app()->user->getState($tokenName);
		$tokenKey   =  md5($_SERVER['REQUEST_URI']); // 标识当前页面唯一性
		if(isset($tokenArr[$tokenKey])) {// 相同页面不重复生成session
			$tokenValue = $tokenArr[$tokenKey];
		}else{
			$tokenValue = $tokenType(microtime(TRUE));
			$tokenArr[$tokenKey]   =  $tokenValue;
		}
		\Ivy::app()->user->setState($tokenName,$tokenArr);
		$token      =  '<input type="hidden" name="'.$tokenName.'" value="'.$tokenKey.'_'.$tokenValue.'" />';
		return $token;
	}

	/**
	 * 内置标签解析 支持 if、 elseif、 foreach 
	 * @param  [type] &$content [description]
	 * @return [type]           [description]
	 $content = '
<body>
    <p>{$name->abc}</p>
    <p>{$name['abc']}</p>
	<p>{$name}</p>
	<p>{$age}</p>
	{if $age > 18}
		<p>已成年</p>
	{else if $age < 10}
		<p>小毛孩</p>
	{/if}
	{foreach $friends as $v} 
		<p>{$v}</p>
	{/foreach}
</body>';
	*/
    private function tagsCompiler(&$content) {
		$_patten = [
            '#\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)->([\w]+)\}#',
            '#\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[.*?\])\}#',
			'#\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}#',
			'#\{\\$this->(.*?)\}#',
			'#\{if (.*?)\}#',
			'#\{(else if|elseif) (.*?)\}#',
			'#\{else\}#',
			'#\{foreach \\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}#',
			'#\{foreach (.*?)\}#',
			'#\{\/(foreach|if)}#',
            '#\{block name=[\'|\"](.*?)[\'|\"]\}#','#\{\/block}#',
            '#\{:(.*?)}#',
		];
		$_translation = [
            '<?php echo \$\\1->\\2; ?>',
            '<?php echo \$\\1\\2; ?>',
			'<?php echo \$\\1; ?>',
			'<?php echo \$this->\\1; ?>',
			'<?php if (\\1) {?>',
			'<?php } else if (\\2) {?>',
			'<?php }else {?>',
			'<?php foreach (\$\\1 as \$k => \$v) {?>',
			'<?php foreach (\\1) {?>',
			'<?php }?>',
            '<!--block \\1 -->','<!--block end-->',
            '<?php echo \\1; ?>',
		];
		$content =  preg_replace($_patten, $_translation, $content);
	}

	/**
     * 写入编译缓存
     * @param string $cacheFile 缓存的文件名
     * @param string $content 缓存的内容
     * @return void|array
     * @throws CException
     */
    private function writeCache($cacheFile, $content)
    {
        // 检测模板目录
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
         //去掉换行、制表等特殊字符，js 和 php代码有问题 注释语法导致
        // $content = preg_replace("/[\t\n\r]+/","",$content);
        // 生成模板缓存文件
        if (false === file_put_contents($cacheFile, $content)) {
            throw new CException('cache write error:' . $cacheFile, 11602);
        }
    }



    /**
     * 检查编译缓存是否有效
     * @param string  $cacheFile 缓存的文件名
     * @return boolean
     */
    public function checkCache($cacheFile)
    {
        // 缓存文件不存在, 直接返回false
        if (!file_exists($cacheFile)) {
            return false;
        }
        $cacheTime = $this->config['cache_time'];
        if(!empty($cacheTime)){
            return false;
        }
        // 永久有效
        if($cacheTime == -1){
            return true;
        }
        if ($_SERVER['REQUEST_TIME'] > filemtime($cacheFile) + $cacheTime) {
            // 缓存是否在有效期
            return false;
        }
        return true;
    }
}
