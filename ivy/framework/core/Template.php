<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class Template{
	
    static $view_name = "views";        //模板文件夹名
	protected $data = array();          //仅存放assign的变量
    protected $controller = NULL;
	
	public function __construct($controller){
	   $this->controller = $controller;
	}
	
	/**
	 * 显示模版 直接输出
	 */
	public function display($template='',$ext = '.phtml'){
        $template_path = $this->getViewFile($template,$ext);
        extract($this->data);
        include_once $template_path;
	}

    /**
     * 返回渲染好的html
     */
    public function render($template='',$ext = '.phtml'){
        $template_path = $this->getViewFile($template,$ext);
        extract($this->data);
        ob_start();
        $includeReturn = include $template_path;
        $str = ob_get_clean();
        return $str;
    }
    
    
	/**
	 * 模板文件寻址
	 */
	public function getViewFile($template,$ext = '.phtml'){
		$template=rtrim($template);
		$r = $this->controller->route->getRouter();
		if($template===''){
			$template=$r['action'];
		}
		$template_arr = explode("/",$template);
		if($template_arr[0]==null){
			//绝对路径查找
			$template_arr = array_filter($template_arr);

			if(2===count($template_arr)){
				$template=implode('/', $template_arr);
				$template_path=__PROTECTED__.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
				if(!file_exists($template_path)){
	    			throw new CException('模版-'.$template.'-不存在!');
	    		}
			}
			if(3===count($template_arr)){
				$module=array_shift($template_arr);
				$template=implode('/', $template_arr);

				$template_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
				
				if(!file_exists($template_path)){
	    			throw new CException('模版-'.$template.'-不存在!');
	    		}
			}
			return $template_path;

			var_dump($template_path);die;

		}else{
			//相对路径查找
			$template=implode('/', $template_arr);

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
			return $template_path;
		}
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
	
	
}