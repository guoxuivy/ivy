<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
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
	 * 模版路径-控制器自适应
	 */
	public function template($template){
        $template=rtrim($template);
        $r = $this->controller->route->getRouter();
        if(''===$template){
            $template=$r['controller']."/".$r['action'];
            
        }else{
            $template_arr = array_filter(explode("/",$template));
            if(count($template_arr)==1){
                $template=$r['controller']."/".$template;
            }
        }
        
        return $template;
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
        $template=$this->template($template);
        $r = $this->controller->route->getRouter();
        //自适应分组模式 模板文件寻路
        if(3==count($r)){
            $template_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$r['module'].DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
            if(!file_exists($template_path)){
    			throw new CException('分组的模版不存在!');
    		}
        }
        if(2==count($r)){
            $template_path=__PROTECTED__.DIRECTORY_SEPARATOR.self::$view_name.DIRECTORY_SEPARATOR.$template.$ext;
            if(!file_exists($template_path)){
    			throw new CException('模版不存在!');
    		}
        }
        
        return $template_path;
	}
	/**
	 * 格式化url
	 * $uri     admin/order/index
	 * $param   array("id"=>1)
	 */
	public function url($uri="",$param=array()){ 
        $uri = SITE_URL.'/index.php/?r='.rtrim($uri);
        $param_arr = array_filter($param);
        if(!empty($param_arr)){
            foreach($param_arr as $k=>$v){
                $k=urlencode($k);
                $v=urlencode($v);
                $uri.="&{$k}={$v}";
            }
        }
		return $uri;
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
