<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\cache;
use Ivy\core\CException;
class AbsoluteCache {
	/**
	 * 取得数据库类实例
	 * @static
	 * @access public
	 * @return mixed 返回数据库驱动类
	 */
	public static function getInstance() {
		$args = func_get_args();
		return call_user_func_array(array(__CLASS__, "factory"),$args);
	}

	/**
	 * 加载缓存 支持配置文件
	 * @access public
	 * @param mixed $cache_config 配置信息
	 * @return string
	 */
	protected static function factory($cache_config='') {
		// 读取数据库配置
		if(empty($cache_config)){
			//文件缓存驱动 待扩展
			$cache=null;
			
		}else{
			$class = '\\Ivy\\cache\\'."MCache";
			if(class_exists($class)) {
				$cache = new $class ($cache_config);
			} else {
				throw new CException ('缓存驱动错误：'. $class);
			}
		}
		return $cache;
	}
}
/**
 * 缓存驱动需要实现的接口 
 **/
interface ICache{
	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache or expired.
	 */
	public function get($id);
	/**
	 * Stores a value identified by a key into cache.
	 * If the cache already contains such a key, the existing value and
	 * expiration time will be replaced with the new ones.
	 *
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labelled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function set($id,$value,$expire=0);
	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * Nothing will be done if the cache already contains the key.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labelled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function add($id,$value,$expire=0);
	/**
	 * Deletes a value with the specified key from cache
	 * @param string $id the key of the value to be deleted
	 * @return boolean whether the deletion is successful
	 */
	public function delete($id);
	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 * @return boolean whether the flush operation was successful.
	 */
	public function flush();
}
