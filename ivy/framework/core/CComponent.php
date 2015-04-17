<?php
/**
 * 全局 基类 提供 数据存储的魔术方法 
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class CComponent
{
	//数据存储
	protected $_m = array();
	function __get($name){
		$method="get".ucfirst($name);
		if(method_exists($this,$method)){
			return $this->$method();
		}elseif(property_exists($this,$name)){
			return $this->$name;
		}elseif(isset($this->_m[$name])){
			return $this->_m[$name];
		}else{
			return null;
			//throw new CException( 'Property "'.get_class($this).'.'.$name.'" is not defined.' );
		}
	}
	function __set($name,$value){
		$method="set".ucfirst($name);
		if(method_exists($this,$method)){
			return $this->$method($value);
		}elseif(property_exists($this,$name)){
			return $this->$name=$value;
		}else{
			return $this->_m[$name]=$value;
		}
	}


	/**
	 * 非类型安全
	 * array() 或者 ""都会判定为false
	 */
	public function __isset($name)
	{
		$method="get".ucfirst($name);
		if(method_exists($this,$method)){
			return $this->$method()!=null;
		}elseif(property_exists($this,$name)){
			return $this->$name!=null;
		}elseif(isset($this->_m[$name])){
			return true;
		}else{
			return false;
		}
	}


	public function __unset($name)
	{
		$method="set".ucfirst($name);
		if(method_exists($this,$method)){
			$this->$method(null);
		}elseif(property_exists($this,$name)){
			$this->$name==null;
		}elseif(isset($this->_m[$name])){
			unset($this->_m[$name]);
		}elseif(method_exists($this,'get'.ucfirst($name))){
			throw new CException( 'Property "'.get_class($this).'.'.$name.'" is read only.' );
		}
	}


	// public function __call($name,$parameters)
	// {
	// 	throw new CException( 'method "'.get_class($this).':'.$name.'" is not exist.' );
	// }

	/**
	 * 提供对象行为注入 留待扩展实现AOP
	 * @param  [type] $behaviorObj [description]
	 * @return [type]              [description]
	 */
	public function attachBehavior($behaviorObj){
		$this->_m[] = $behaviorObj;
	}

	public function __call($method,$param){
		foreach($this->_m as $obj){
			if(is_object($obj) && method_exists($obj,$method)){
				$res = call_user_func_array(array($obj,$method),$param);
				return $res;
			}
		}
		throw new CException( 'method "'.get_class($this).'->'.$method.'()" is not exist.' );
	}
}