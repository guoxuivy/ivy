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
final class Console extends CComponent {
	//系统行为类存储 待扩展
	protected $_m = array();
	/**
	 * 数据库实例句柄
	 */
	protected $dbs = array();
	/**
	 * cache
	 */
	protected $cache = NULL;
	/**
	 * 全局配置文件
	 */
	protected $config = NULL;


	/**
	 * 默认不生效 配合 IVY_DEBUG
	 * 用于flash、编辑器等插件 在调试模式下强制不输出sql性能信息
	 */
	protected $noProfile = true;

    /**
     * 加载全局配置文件
     * Application constructor.
     * @param  string $config
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
     * 执行路由 直接输出结果 无返回值
     * @throws CException
     * @throws \ReflectionException
     */
	public function run() {
		$argv = $_SERVER['argv'];
        // 去除命令名
        array_shift($argv);
        if(empty($argv)){
            $this->help();
        }else{
            $class = ucfirst(array_shift($argv));
            $file_path = __PROTECTED__.DS."command".DS.$class.'.php';
            if(is_file($file_path)){
                include_once $file_path;
                $ReflectedClass = new \ReflectionClass('\command\\'.$class);
                $command = $ReflectedClass->newInstanceArgs();
                $command->execute($argv);
            }else{
                throw new CException("no command: ".$class);
            }
        }
		$this->finished();
	}

    public function help(){
        $commands = require_once(__PROTECTED__.DS.'command.php');
        $list = [];
        \Ivy\core\Command::writeln("help:");
        foreach ($commands as $class) {
            $file_path = __PROTECTED__.DS."command".DS.$class.'.php';
            if(is_file($file_path)){
                include_once $file_path;
                $ReflectedClass = new \ReflectionClass('\command\\'.$class);
                $_obj = $ReflectedClass->newInstanceArgs();
                $list[$_obj->name] = $_obj->description;

                $len = strlen($_obj->name);
                \Ivy\core\Command::write($_obj->name.str_repeat(' ', 30));
                \Ivy\core\Command::writeln( str_repeat(chr(8), $len) . $_obj->description);
            }
        }
    }

	/**
	 * 正常结束处理
	 */
	public function finished() {
	}
	
}
