<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework 加特技 duang
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
use \Ivy\cache\AbsoluteCache;
use \Ivy\db\AbsoluteDB;
final class Application extends CComponent {
	//系统行为类存储 待扩展
	protected $_m = array();
	/**
	 * 数据库实例句柄
	 */
	protected $dbs = array();
	/**
	 * 登录用户
     * @var User
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
	 * 当前控制器路由对象（非widget路由）保存，
	 */
	protected $_route = NULL;

	/**
	 * 默认不生效 配合 IVY_DEBUG
	 * 用于flash、编辑器等插件 在调试模式下强制不输出sql性能信息
	 */
	protected $noProfile = false;

    /**
     * 加载全局配置文件
     * Application constructor.
     * @param  string $config
     * @throws CException
     */
    /**
     * Application constructor.
     * @param $config
     * @throws CException
     */
	public function __construct($config){
		\Ivy::setApplication($this);//保存句柄
		$config = require_once($config);
		$this->C($config);
	}

    /**
     * 数据库句柄对象
     * 支持多数据库连接
     * $config 为配置数组
     * @param null $config
     * @return mixed
     * @throws CException
     */
	public function getDb($config=null) {
		$config=is_null($config)?$this->C('db_pdo'):$config;
		if(empty($config))
			throw new CException ( '未配置数据！'); 
		$key=md5(serialize($config));
		if(isset($this->dbs[$key]) && $this->dbs[$key] instanceof AbsoluteDB){
			return $this->dbs[$key];
		}else{
			$this->dbs[$key] = AbsoluteDB::getInstance($config);
			return $this->dbs[$key];
		}
	}
	/**
	 * 数据库释放
	 */
	public function dbClose() {
		foreach ($this->dbs as $db) {
			$db->close();
		}
	}

	public function closeProfile(){
	    $this->noProfile = true;
    }

    /**
     * 当前实例的配置信息修改、读取 支持全部、局部更新，全部、局部查询
     * @param null $key
     * @param null $config
     * @return array|null
     */
	public function C($key=null,$config=null) {
		if(is_array($key))
			return $this->config=$key;
		if(is_null($key))
			return $this->config;
		if(is_null($config))
			return $this->config[$key]?$this->config[$key]:null;
		return  $this->config[$key]=$config;
	}



	/**
	 * 缓存句柄对象
	 * memcache自动支持集群
	 */
	public function getCache() {
		$config=$this->C('memcache');
		if($this->cache instanceof AbsoluteCache){
			return $this->cache;
		}else{
			$this->cache = AbsoluteCache::getInstance($config);
			return $this->cache;
		}
	}


    /**
     * 登录用户句柄对象
     * @return User|null
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
     * widget 小部件 按命名空间
     * @param string $name 小部件名称
     * @param array $param 自定义参数
     * @return mixed
     * @throws CException
     */
	public function widget($name,$param=array()) {
		try{
			\Ivy::importWidget($name);
			$class = str_replace('/', '\\', $name);
			$ReflectedClass = new \ReflectionClass($class."Widget"); // 2级控制器检测 非分组模式
		}catch(\ReflectionException $e){
			throw new CException ( $name . '-不存在此widget！');
		}
		$widget_obj = $ReflectedClass->newInstanceArgs();
		return $widget_obj->run($param);
	}

    /**
     * hook 与 run 类似用户控制器之间的调用（系统钩子）
     * 自动适配分组模式 优先适配普通模式的控制器
     * @param string/array $routerStr 路由参数
     * @param array $param 自定义参数
     * @return mixed
     * @throws CException
     */
	public function hook($routerStr,$param=array()) {
		$route = new Route();
		if(is_array($routerStr)) {
            $routerStr=implode("/",$routerStr);
        }
		$route->start($routerStr,$param);
		return $this->dispatch($route);
	}

    /**
     * 执行路由 直接输出结果 无返回值
     * @throws CException
     */
	public function run() {
		$route = new Route();
		$route->start();
		$this->_route=$route;
		$this->dispatch($route);
		$this->finished();
	}

    /**
     * 分发 自动适配分组模式 优先适配普通模式的控制器
     * @param  Route $routerObj 路由对象
     * @return mixed 附带参数数组
     * @throws CException
     *
     * 'module' => 'admin'          //分组（非必须）
     * 'controller' => 'roder'      //控制器（必须）
     * 'action' => 'index'          //方法（必须）
     */
	public function dispatch(Route $routerObj) {
		$param=$routerObj->param;
		$router=$routerObj->getRouter();
		$module=$router['module'];
		$class=ucfirst($router['controller'])."Controller";	//控制器类名首字母大写
		$action=$router['action'].'Action';
		if(''==$module){
			try{
				//优先适配2级，不存在则适配3级
				$ReflectedClass = new \ReflectionClass($class); // 2级控制器检测 非分组模式
			}catch(\Exception $e){
				//试图适配分组模式
				$routerObj->setRouter(array('module'=>$router['controller'],'controller'=>$router['action']));
				return $this->dispatch($routerObj);
			}
		}else{
			try{
				//检测3层分组模式下是否存在控制器，只在3级下抛出异常
				$class=$module."\\".$class; 
				$ReflectedClass = new \ReflectionClass($class);
			}catch(\Exception $e){
				throw new CException ( $router['module'].'/'.$router['controller'] . '-不存在！'); 
			}
		}
		
		$controller_obj = $ReflectedClass->newInstanceArgs(array($routerObj));
		if($ReflectedClass->hasMethod("actionBefore")){
			 $controller_obj->actionBefore();
		}
		$_before=str_replace('Action','Before',$action);
		if($ReflectedClass->hasMethod($_before)){
			$this->_doMethod($controller_obj, $_before, $param);
		}
		if(!$ReflectedClass->hasMethod($action) && !$ReflectedClass->hasMethod("emptyAction")){
            throw new CException ( '访问action不存在！');
		}
		$result = $this->_doMethod($controller_obj, $action, $param);
		$_after=str_replace('Action','After',$action);
		if($ReflectedClass->hasMethod($_after)){
			$this->_doMethod($controller_obj, $_after, $param);
		}
		if($ReflectedClass->hasMethod("actionAfter")){
			 $controller_obj->actionAfter();
		}
		return $result;
	}

    /**
     * 自动适配参数 并且执行
     * @param $obj
     * @param $method
     * @param array $args
     * @return mixed
     * @throws CException
     */
	private function _doMethod($obj, $method, array $args = array()) {
        try{
            $reflection = new \ReflectionMethod($obj, $method);
            $pass = array();
            foreach($reflection->getParameters() as $param){
                if(isset($args[$param->getName()])){
                    $pass[] = $args[$param->getName()];
                }else{
                    $pass[] = $param->getDefaultValue()?:null;
                }
            }
            return $reflection->invokeArgs($obj, $pass);
        }catch(\ReflectionException $e){
            if(method_exists($obj,"emptyAction")){
                return $obj->emptyAction($method,$args);
            }
            throw new CException ( $e->getMessage() );
        }

	}


	/**
	 * 正常结束处理
	 */
	public function finished() {
	}
	
}
