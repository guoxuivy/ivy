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
use Ivy\core\CException;
class Template{
	/**
	 * 模版处理类
	 * @var string
	 */
	static $view_name = "views";        //模板文件夹名
	protected $data = array();          //仅存放assign的变量
	protected $controller = NULL;

	public function __construct($controller){
		$this->controller = $controller;
	}

	/**
	 * 显示输出 加载布局文件
	 */
	public function display($template='',$ext = '.phtml'){
		$output=$this->render($template);
		if($this->controller->layout!=null){
			$output=$this->render($this->controller->layout,array('content'=>$output));
		}
		//表单token
		$this->tagToken($output);
		echo $output;
	}

	/**
	 * 返回渲染好的html
	 */
	public function render($template='',$data=array(),$ext='.phtml'){
		$template_path = $this->getViewFile($template,$ext);
		$data=array_merge($this->data,$data);
		extract($data,EXTR_OVERWRITE);
		ob_start();
		include $template_path;
		$str = ob_get_clean();
		return $str;
	}



	/**
	 * 模板文件寻址
	 * /开头为绝对路径寻址
	 * 其它为相对路径寻址
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
				if(isset($r['module'])){
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
	 * @param  [type] $template [description]
	 * @param  string $ext      [description]
	 * @return [type]           [description]
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
	 * assign 
	 * 模版变量传递
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
	 * $name 文件路径+文件名称 （public之后的部分）
	 * $type 文件类型 image,js,css,data
	 */
	public function basePath($name){
		return SITE_URL.'/'.$name;
	}

	/**
	 * 设置属性，供模版使用
	 */
	public function __set($k,$v){
		$this->$k = $v;
	}


	/**
	 * token注入
	 * @param  [type] &$content [description]
	 * @return [type]           [description]
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

	// 创建表单令牌
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
	 * HTML select 简写
	 * @param [type] $arr    [description]
	 * @param [type] $get    [description]
	 * @param [type] $config [description]
	 */
	public function dropDownList($name=null,$arr,$get,$config=null)
	{
		if($name)
			$config['name']=$name;
		$sel_html='<select {sel_config}>
				    {op_html}
					</select>';
		$op_html='<option value="">--请选择--</option>';
		foreach ((array)$arr as $key => $value) {
			$op_html.="<option value=\"{$key}\" ".(($get==$key)?'selected':'')." {op_config}>{$value}</option>";
		}
		$op_config='';
		if (isset($config['op_config'])) {
			foreach ((array)$config['op_config'] as $key => $value) {
				$op_config.=$key.'="'.$value.'" ';
			}
			unset($config['op_config']);
		}
		$op_html=@str_replace('{op_config}',$op_config,$op_html);
		$sel_config='';
		if (isset($config)) {
			foreach ((array)$config as $key => $value) {
				$sel_config.=$key.'="'.$value.'" ';
			}
			unset($config);
		}
		$sel_html=@str_replace('{sel_config}',$sel_config,$sel_html);
		$sel_html=@str_replace('{op_html}',$op_html,$sel_html);
		return $sel_html;
	}
	
}