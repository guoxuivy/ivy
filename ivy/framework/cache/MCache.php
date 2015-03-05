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
use Ivy\core\lib\FlexiHash;
use Ivy\core\CException;
class MCache extends AbsoluteCache implements ICache{
	//连接池句柄
	private $_memcache = array();
	//一致性哈希对象
	private $_FlexiHash = null;

	public function __construct($config){
		try {
			$FlexiHash = new FlexiHash($config);
			$this->_FlexiHash = $FlexiHash;
		} catch ( \Exception $e ) {
			throw new CException ( $e->getMessage() );
		}
	}
	/**
	 * 获取对应的memcache服务器 连接句柄
	 **/
	private function _connectMemcache($key){
		$config = $this->_FlexiHash->get($key);
		if (!isset($this->_memcache[$config])){
			$this->_memcache[$config] = new \Memcache;
			list($host, $port) = explode(":", $config);
			$this->_memcache[$config]->connect($host, $port);
		}
		return $this->_memcache[$config];
	}
	/**
	 * 测试探针 获取key对应的物理节点
	 **/
	public function getConfigByKey($key){
		return $config = $this->_FlexiHash->get($key);
	}

	/**
	 * $expire 单位为秒
	 **/
	public function set($key, $value, $expire=0){
		return $this->_connectMemcache($key)->set($key, json_encode($value), 0, $expire);
	}

	public function get($key){
		$value = $this->_connectMemcache($key)->get($key, true);
		if($value!==false)
			$value=json_decode($value);
		return $value;
	}

	public function add($key, $vakue, $expire=0){
		return $this->_connectMemcache($key)->add($key, json_encode($value), 0, $expire);
	}

	public function delete($key){  
		return $this->_connectMemcache($key)->delete($key);
	}
    
    public function flush(){
        foreach($this->_memcache as $memcache_obj){
            $memcache_obj->flush();
        }
		return true;
	}
}