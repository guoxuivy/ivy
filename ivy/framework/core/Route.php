<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;

class Route {
	
	protected $app = NULL;
    
    //默认路由
    static $route= array(
			'controller' =>'index',
			'action'	=> 'index'
	);
	
	public function __construct($app = NULL){
		if(isset($app) && $app instanceof Application){
			$this->app = $app;
		}else{
			throw new CException('参数错误');
		}
	}
	
	/**
	 * 路由 处理  
     * @return 标准路由数组
	 */
	public function solve($routerStr=''){
		return $this->analyzeRoute($routerStr);
	}
    
    /**
	 * 路由 处理  
     * @return 标准路由数组
	 */
	public function start(){
        $routerStr=isset($_GET['r'])?$_GET['r']:'';
		return $this->analyzeRoute($routerStr);
	}
    
    
    /**
	 * 获取路由配置
	 */
	private function getRouter(){
		try{
            return $this->app->config['route'];
        }
        catch (CException $e)
        {
            return self::$route;
        }
	}
	
	/**
	 * 解析路由信息
	 */
	private function analyzeRoute($routerStr=''){
       
        if($routerStr===''){
            $route_tmp=array_values($this->getRouter());
        }else{
            $route_str=rtrim($routerStr);
            $route_tmp = explode('/', $route_str);
        }
        
        
        $route_tmp=array_filter($route_tmp);
        if(count($route_tmp) == 3){ //分组模式
			$route_info = array();
            $route_info['module'] = $route_tmp[0];
			$route_info['controller'] = $route_tmp[1];
			$route_info['action'] = $route_tmp[2];
			return $route_info;
		}
		if(count($route_tmp) == 2){ //普通模式
			$route_info = array();
			$route_info['controller'] = $route_tmp[0];
			$route_info['action'] = $route_tmp[1];
			return $route_info;
		}else if(count($route_tmp) == 1){ //普通模式 默认action
			if($route_tmp[0] != ''){
				$route_info = array();
    			$route_info['controller'] = $route_tmp[0];
                $t_r=$this->getRouter();
    			$route_info['action'] = $t_r['action'];
    			return $route_info;
			}else{
				throw new CException('路由错误');
			}
			
		}else{
			throw new CException('路由错误');
		}
	}
}