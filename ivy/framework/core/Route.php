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

class Route {
	
    //默认路由
    static $_route= array(
			'controller' =>'index',
			'action'	=> 'index'
	);
    
    protected $route = NULL;
	
	public function __construct(){
	}
	
    
    /**
	 * 路由 处理  
     * 标准路由数组
	 */
	public function start($routerStr=''){
		$this->analyzeRoute($routerStr);
	}
    
    /**
	 * 获取当前
	 */
	public function getRouter(){
		return $this->route;
	}
    /**
	 * 获取当前
	 */
	public function setRouter($route){
        $c_route=$this->getConfigRouter();
        if(is_array($route)){
            $r=array();
            if(isset($route['module'])) $r['module']=$route['module'];
            $r['controller']=isset($route['controller'])?$route['controller']:$c_route['controller'];
            $r['action']=isset($route['action'])?$route['action']:$c_route['action'];
            $this->route=$r;
        }
        if(is_string($route)){
            $this->analyzeRoute($route);
        }
	}
    
    /**
	 * 获取路由配置
	 */
	private function getConfigRouter(){
        $_config = \Ivy::app()->config;
        if(isset($_config['route'])&&!empty($_config['route'])){
            return $_config['route'];
        }
		return self::$_route;
	}
	
	/**
	 * 解析路由信息
     * @routerStr  string 路由参数 m/c/a
	 */
	private function analyzeRoute($routerStr=''){
       
        if($routerStr===''){
            $route_tmp=array_values($this->getConfigRouter());
        }else{
            $route_str=rtrim($routerStr);
            $route_tmp = explode('/', $route_str);
        }
        $route_tmp=array_filter($route_tmp);
        $route_info = array();
        if(count($route_tmp) == 3){ //分组模式
            $route_info['module'] = $route_tmp[0];
			$route_info['controller'] = $route_tmp[1];
			$route_info['action'] = $route_tmp[2];
		}
		if(count($route_tmp) == 2){ //普通模式
			$route_info['controller'] = $route_tmp[0];
			$route_info['action'] = $route_tmp[1];
		}
        if(count($route_tmp) == 1){ //普通模式 默认action
			if($route_tmp[0] != ''){
    			$route_info['controller'] = $route_tmp[0];
                $c_r=$this->getConfigRouter();//采用配置action
    			$route_info['action'] = $c_r['action'];
			}else{
				throw new CException('路由参数为空');
			}
		}
        if(!empty($route_info)){
            $this->route=$route_info;
        }else{
            throw new CException('路由错误');
        }
    
	}
}