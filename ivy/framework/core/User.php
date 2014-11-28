<?php
/**
 * 登录用户信息和session操作
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
class User implements \IteratorAggregate, \ArrayAccess
{
    const STATES_VAR='__states';
    private $_keyPrefix;
    protected $attributes =null; //保存数据库中用户信息
    
    
    public function __construct(){
        @session_start();
        if(!$this->isGuest){
            $this->attributes=$this->getState('__attributes');
        }
    }
    
    /**
     *支持对象的数组用法 ArrayAccess IteratorAggregate方法实现 
     **/
    function offsetGet($offset){
        return $this->$offset;
    }
    function offsetSet($offset,$item){
        return $this->$offset=$item;
    }
    public function offsetExists($offset) {
        return property_exists($this,$offset);
    }
    public function offsetUnset($offset) {
        unset($this->$offset);
    }
    public function getIterator()
	{
		$attributes=$this->attributes;
		return new \ArrayIterator($attributes);
	}
    
    
    function __get($proName){
        $method="get".ucfirst($proName);
        if(method_exists($this,$method)){
            return $this->$method();
        }elseif(property_exists($this,$proName)){
            return $this->$proName;
        }elseif(isset($this->attributes[$proName])){
            return $this->attributes[$proName];
        }else{
            return false;
        }
    }
    function __set($proName,$value){
        $method="set".ucfirst($proName);
        if(method_exists($this,$method)){
            return $this->$method($value);
        }elseif(property_exists($this,$proName)){
            return $this->$proName=$value;
        }elseif(in_array($proName,array_keys($this->attributes))){
            return $this->attributes[$proName]=$value;
        }else{
            return false;
        }
    }
    
    //session 用户登录前缀
    public function getStateKeyPrefix()
	{
		if($this->_keyPrefix!==null){
            return $this->_keyPrefix;
		}else{
            $this->_keyPrefix=md5('Ivy.'.get_class($this));
            return $this->_keyPrefix;
		}
			
	}
    
    public function getState($key,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
	}
    
    public function setState($key,$value,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		if($value===$defaultValue)
			unset($_SESSION[$key]);
		else
			$_SESSION[$key]=$value;
	}
    
    public function clearStates()
	{
		$keys=array_keys($_SESSION);
		$prefix=$this->getStateKeyPrefix();
		$n=strlen($prefix);
		foreach($keys as $key)
		{
			if(!strncmp($key,$prefix,$n))
				unset($_SESSION[$key]);
		}
	}
    
    
    
    public function getIsGuest()
	{
		return $this->getState('__id')===null;
	}

	/**
	 * 用户登录标示 If null, it means the user is a guest.
	 */
	public function getId()
	{
		return $this->getState('__id');
	}

	public function setId($value)
	{
		$this->setState('__id',$value);
	}
    
    public function login($user)
	{
		$this->setState('__id',$user->id);
        $this->setState('__attributes',$user->attributes);
        $this->attributes=$user->attributes;
        
	}
    public function logout()
	{
		$this->clearStates();
	}
    
    
    /**
	 * 全局返回路径
	 */
    public function getReturnUrl($defaultUrl=null)
	{
		return $this->getState('__returnUrl',$defaultUrl);
	}
	public function setReturnUrl($value)
	{
		$this->setState('__returnUrl',$value);
	}
    
    
   
	
	
    
    
}
