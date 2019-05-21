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
        'layout_item'        => '{__CONTENT__}', // 布局模板的内容替换标识
    ];

	/**
	 * 模版处理类
	 * @var string
	 */
	static $view_name = "views";        //模板文件夹名
	protected $data = array();          //仅存放assign的变量
	protected $controller = NULL;

	public function __construct(&$controller){
		$this->controller = $controller;
		$this->init();
	}

	public function init(){}

    /**
     * 显示输出 加载布局文件
     * @param string $template
     * @param string $ext
     * @throws CException
     */
	public function display($template='',$ext = '.phtml'){
        $cacheFile = $this->checkAndBuildTemplateCache($template,true,$ext);
        $output = $this->ob($this->data,$cacheFile);
		//表单token
		$this->tagToken($output);
		echo $output;
	}

    /**
     * 模板文件加载到ob
     * @param $data
     * @param $cacheFile
     * @return string
     */
	protected function ob($data,$cacheFile){
        extract($data,EXTR_OVERWRITE);
        ob_start();
        include $cacheFile;
        $output = ob_get_clean();
        return $output;
    }

    /**
     * 返回渲染好的html
     * @param string $template
     * @param array $data
     * @param string $ext
     * @return string
     * @throws CException
     */
	public function render($template='',$data=array(),$ext='.phtml'){
        $cacheFile = $this->checkAndBuildTemplateCache($template,false,$ext);
		$data=array_merge($this->data,$data);
        $output = $this->ob($data,$cacheFile);
		return $output;
	}

    /**
     * 检查并生成模板缓存文件
     * @param $template
     * @param bool $layout  是否检查布局文件
     * @param string $ext
     * @throws CException
     * @return string
     */
    public function checkAndBuildTemplateCache($template,$layout=true,$ext='.phtml'){
        $template_path = $this->getViewFile($template,$ext);
        $cacheFile = __RUNTIME__.DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.md5($template_path.$this->controller->layout).$ext;
        if (!self::checkCache($cacheFile)) {
            // 缓存无效 重新模板编译
            $content = file_get_contents($template_path);
            if($layout && $this->controller->layout!=null){
                $layout_path = $this->getViewFile($this->controller->layout,$ext);
                $layout_content = file_get_contents($layout_path);
                $content = str_replace($this->config['layout_item'],$content,$layout_content);
            }
            self::tagsCompiler($content);
            self::writeCache($cacheFile, $content);
        }
        return $cacheFile;
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
				$template=implode(DIRECTORY_SEPARATOR, $template_arr);
				$template_path=__PROTECTED__.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
				if(!file_exists($template_path)){
					throw new CException('模版-'.$template.'-不存在!');
				}
			}
			if(3===count($template_arr)){
				$module=array_shift($template_arr);
				$template=implode(DIRECTORY_SEPARATOR, $template_arr);
				$template_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
				
				if(!file_exists($template_path)){
					throw new CException('模版-'.$template.'-不存在!');
				}
			}
			return $template_path;

		}else{
			//相对路径查找
			$template=implode(DIRECTORY_SEPARATOR, $template_arr);
			if(3===count($template_arr)){
				$module=array_shift($template_arr);
				$template=implode(DIRECTORY_SEPARATOR, $template_arr);
				$template_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
				if(!file_exists($template_path)){
					throw new CException('模版-'.$template.'-不存在!');
				}
			}else{
				if(1==count($template_arr)) $template=$r['controller'].DIRECTORY_SEPARATOR.$template_arr[0];
				if(isset($r['module']) && !empty($r['module'])){
					$template_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$r['module'].DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
					if(!file_exists($template_path)){
						throw new CException('模版-'.$template.'-不存在!');
					}
				}else{
					$template_path=__PROTECTED__.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
					if(!file_exists($template_path)){
						throw new CException('模版-'.$template.'-不存在!');
					}
				}
			}
			return $template_path;
		}
	}

    /**
     * 引入其他模版文件
     * @param $template
     * @param string $ext
     * @throws CException
     */
	public function import($template,$ext = '.phtml'){
		$template_path = $this->getViewFile($template,$ext);
		include $template_path;
	}


	/**
	 * 格式化url
	 * $uri     admin/order/index
	 * $param   array("id"=>1)
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
		if(\Ivy::app()->C('token')) {
			if(strpos($content,'{__TOKEN__}')) {
                // 指定表单令牌隐藏域位置
                $content = str_replace('{__TOKEN__}',$this->buildToken(),$content);
            }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {
                // 智能生成表单令牌隐藏域
                $content = str_replace($match[0],$this->buildToken().$match[0],$content);
            }
		}else{
			$content = str_replace('{__TOKEN__}','',$content);
		}
	}

    /**
     * 创建表单令牌
     * @return string
     */
	private function buildToken() {
		$tokenName  = '__hash__';
		$tokenType  = 'md5';
		$tokenArr=\Ivy::app()->user->getState($tokenName);
		
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
    public static function tagsCompiler(&$content) {
		$_patten = [
			'#\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}#',
			'#\{\\$this->(.*?)\}#',
			'#\{if (.*?)\}#',
			'#\{(else if|elseif) (.*?)\}#',
			'#\{else\}#',
			'#\{foreach \\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}#',
			'#\{foreach (.*?)\}#',
			'#\{\/(foreach|if)}#',
		
		];
		$_translation = [
			'<?php echo \$\\1; ?>',
			'<?php echo \$this->\\1; ?>',
			'<?php if (\\1) {?>',
			'<?php } else if (\\2) {?>',
			'<?php }else {?>',
			'<?php foreach (\\1 as \$k => \$v) {?>',
			'<?php foreach (\\1) {?>',
			'<?php }?>',
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
    public static function writeCache($cacheFile, $content)
    {
        // 检测模板目录
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
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
    public static function checkCache($cacheFile)
    {
        // 缓存文件不存在, 直接返回false
        if (!file_exists($cacheFile)) {
            return false;
        }
        $cacheTime = \Ivy::app()->C('template_cache_time');
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