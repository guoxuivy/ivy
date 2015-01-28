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
final class Application extends CComponent {
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
	protected $config = NULL;
    /**
	 * 当前路由副本保存，共loaderClass使用
	 */
    protected $temp_route = NULL;
    

    /**
	 * 加载全局配置文件
	 */
    public function __construct($config){
        \Ivy::setApplication($this);//保存句柄
        $config = require_once($config);
        $this->config=$config;
    }
    
    /**
     * 数据库句柄对象
     * 
     * 变量类名 无法应用命名空间~~~!
     */
    public function getDb() {
        if($this->db instanceof \Ivy\db\AbsoluteDB){
            return $this->db;
        }else{
            $class_arr=explode(":",$this->config['db_pdo']['dsn']);
            $class="\\Ivy\\db\\pdo\\".$class_arr[0];
            $this->db = new $class ($this->config['db_pdo']);
            return $this->db;
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
	 * 登录用户句柄对象
	 */
	public function getUser() {
        if($this->user instanceof User){
        	return $this->user;
        }else{
            $this->user = new User();
            return $this->user;
        }
	}
    
    /**
	 * widget 与 run 类似直接输出 不返回
     * @comment 自动适配分组模式 优先适配普通模式的控制器
     * @param $routerStr  路由参数  
     * @param $param array 自定义参数
	 */
	public function widget($routerStr,$param=array()) {
        $route = new Route();
        if(is_array($routerStr)) $routerStr=implode("/",$routerStr);
        $route->start($routerStr);
        return $this->dispatch($route,$param);
	}
    
	
    /**
     * 执行路由
     * 直接输出结果 无返回值
     * @return null
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
                return $this->dispatch($routerObj,$param);
            }
        }
        try{
            //两级或者三级 2级则必定存在此控制器，3级则不一定
            if(''!==$module) $class=$module."\\".$class; 
            $ReflectedClass = new \ReflectionClass($class);
        }catch(CException $e){
            throw new CException ( $router['module'] . '-分组不存在！'); 
        }
        
        //widget的参数用$_REQUEST传递
        if(!empty($param)){
            $_REQUEST = array_merge($_REQUEST,$param);
        }
        $controller_obj = $ReflectedClass->newInstanceArgs(array($routerObj));
        if($ReflectedClass->hasMethod("actionBefore")){
             $controller_obj->actionBefore();
        }
        return $controller_obj->$action();
	}
    
    /**
     * 后去runtime路径
     * @return string
     */
    public function getRuntimePath() {
		return __PROTECTED__.DIRECTORY_SEPARATOR.'runtime';
	}
	
    /**
     * 正常结束处理
     * @return [type] [description]
     */
	public function finished() {
	}
	
}