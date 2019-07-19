<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * 页面小插件
 * 最好不使用布局文件 当前主控制器路由对象注入
 */
namespace Ivy\core;
abstract class Widget extends Controller {
	//模版变量
	protected $data = array();
	public function __construct() {
		//注入当前主控制器路由
		$this->attachBehavior(\Ivy::app()->_route);
		$this->init();//用于重写
	}


    /**
     * widget必须包含run()方法，只允许一个参数
     * @param $data
     * @return mixed
     */
	abstract function run($data);

    /**
     * render 返回渲染好的html 模版文件名只能小写
     * 当前目录下 view 中寻址
     * render 返回渲染好的html 模版文件名只能小写
     * @param array $data
     * @param string $ext
     * @return string
     * @throws CException
     */
	public function render($data=[],$ext='.phtml'){
		$className = get_class($this);
		$classFile = str_replace("\\",DS,$className);//命名空间寻址 兼容linux
		$filename= substr(strtolower($classFile),0,-6).$ext;
		$filename_arr = explode(DS, $filename);
		$last = array_pop($filename_arr);
		array_push($filename_arr,'view',$last);
		$filename=implode(DS,$filename_arr);
		$template_path = __PROTECTED__.DS."widgets".DS.$filename;
		if(!file_exists($template_path)){
			throw new CException('widget模版-'.$template_path.'-不存在!');
		}
        $data = array_merge($this->data,$data);
        $template = new Template($this);
        $template->assign($data);
        $cacheFile = $template->checkAndBuildTemplateCache($template_path,false,$ext);
        $output = $template->ob($cacheFile);
        return $output;
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
}
