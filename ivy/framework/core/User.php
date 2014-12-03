<?php
/**
 * 登录用户信息和session操作
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 * 
 */
namespace Ivy\core;
class User extends Model
{
    private $_keyPrefix;
    
    public function __construct(){
        @session_start();
        if(!$this->isGuest){
            $this->attributes=$this->getState('__attributes');
        }
    }
    
    public function tableName(){
        return false;
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
    
    
    /**
     * 检测是否为登录用户 
     **/
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
