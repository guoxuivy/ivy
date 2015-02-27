<?php
/**
 * 全局 基类 提供 数据存储的魔术方法 
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class CComponent
{
	//数据存储
	protected $_m;
	function __get($name){
		$method="get".ucfirst($name);
		if(method_exists($this,$method)){
			return $this->$method();
		}elseif(property_exists($this,$name)){
			return $this->$name;
		}elseif(isset($this->_m[$name])){
			return $this->_m[$name];
		}else{
			throw new CException( 'Property "'.get_class($this).'.'.$name.'" is not defined.' );
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


	public function __call($name,$parameters)
	{
		throw new CException( 'method "'.get_class($this).':'.$name.'" is not exist.' );
	}
}