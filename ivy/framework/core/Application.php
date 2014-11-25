<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
final class Application {
	static $app = NULL;
    /**
	 * 数据库实例句柄
	 */
    protected $db = NULL;
    /**
	 * 登录用户
	 */
    protected $user = NULL;
    /**
	 * cache
	 */
    protected $cache = NULL;
    /**
	 * 全局配置文件
	 */
	protected $config = array ();
    /**
	 * 当前路由副本保存，共loaderClass使用
	 */
    public $temp_route=array();
    

    /**
	 * 加载全局配置文件
	 */
    private function __construct($config){
        $this->config=$config;
    }
    
    /**
	 * 实例化（单例模式）
	 */
	static public function init($config = NULL) {
		if (self::$app instanceof Application) {
			$app = self::$app;
		} else {
            $config = require_once($config);
			$app = new Application($config);
		}
        self::$app=$app;
		return $app;
	}
    
    function __get($proName){
        $method="get".ucfirst(strtolower($proName));
        if(method_exists($this,$method)){
            return $this->$method();
        }else{
            return $this->$proName;
        }
    }
    
    
    /**
	 * 缓存句柄对象
	 */
	public function getCache() {
        if($this->cache instanceof Cache){
        	return $this->cache;
        }else{
            $this->cache = new Cache ($this->config['memcache']);
            return $this->cache;
        }
	}
    
    /**
	 * 数据库句柄对象
     * 
     * 变量类名 无法应用命名空间~~~!
	 */
	public function getDb() {
        if($this->db instanceof AbsoluteDB){
        	return $this->db;
        }else{
            $class_arr=explode(":",$this->config['db_pdo']['dsn']);
            $class="Ivy\\db\\pdo\\".$class_arr[0];
        	$this->db = new $class ($this->config['db_pdo']);
            return $this->db;
        }
	}
    
    /**
	 * 登录用户句柄对象
	 */
	public function getUser() {
        if($this->user instanceof AbsoluteDB){
        	return $this->user;
        }else{
            $this->user = new User ();
            return $this->user;
        }
	}
    
    /**
	 * widget 与 run 类似直接输出 不返回
     * @param $routerStr  路由参数  
     * @comment 自动适配分组模式 优先适配普通模式的控制器
     * @param $param array 自定义参数
	 */
	public function widget($routerStr,$param=array()) {
        $route = new Route();
        if(is_array($routerStr)) $routerStr=implode("/",$routerStr);
        $route->start($routerStr);
        $this->dispatch($route,$param);
	}
    
    

    
	
	/**
	 * 路由执行
	 */
	public function run() {
		$route = new Route();
        $routerStr=isset($_GET['r'])?$_GET['r']:"";
        $route->start($routerStr);
        $this->dispatch($route);
        $this->finished();
	}
	
	/**
	 * 分发 
     * @param $routerObj obj 路由对象
     * @param $param array 附带参数数组 
     * @comment 自动适配分组模式 优先适配普通模式的控制器
     * 
     * 'module' => 'admin'          //分组（非必须）
     * 'controller' => 'roder'      //控制器（必须）
     * 'action' => 'index'          //方法（必须）
	 */
	public function dispatch($routerObj,$param=array()) {
        //$this->beforeDispatch();
        $router=$this->temp_route=$routerObj->getRouter();
        $module=isset($router['module'])?strtolower($router['module']):"";
        $class=ucfirst(strtolower($router['controller']))."Controller";
        $action=strtolower($router['action']).'Action';
        if(''===$module){
            try{
                $ReflectedClass = new \ReflectionClass($class); // 2级控制器检测 非分组模式
            }catch(CException $e){
                //试图适配分组模式
                $routerObj->setRouter(array('module'=>$router['controller'],'controller'=>$router['action']));
                $this->dispatch($routerObj);
                exit();
            }
        }
        try{
            //两级或者三级 2级则必定存在此控制器，3级则不一定
            if(''!==$module) $class=$module."\\".$class; 
            $ReflectedClass = new \ReflectionClass($class);
        }catch(CException $e){
            throw new CException ( $router['module'] . '-分组不存在！'); 
        }
        $hasMethod = $ReflectedClass->hasMethod($action);
        if($hasMethod){
            //widget的参数用$_REQUEST传递
            if(!empty($param)){
                $_REQUEST = array_merge($_REQUEST,$param);
            }
            return $ReflectedClass->newInstanceArgs(array($routerObj))->$action();//实例化
        }
        throw new CException ( $class . '控制器中没有方法：' . $action );    
     
	}
    
    
	
	/**
	 * 结束 处理
	 */
	public function finished() {
	
	}
	
	/**
	 * call
	 */
	public function __call($method, $arguments) {
		throw new CException ( "访问的模块（{$method}）不存在！" );
	}
}