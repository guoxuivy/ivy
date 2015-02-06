<?php
/**
 * 登录用户信息和session操作
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * 
 */
namespace Ivy\core;
class User extends Model
{
    static $_keyPrefix; 
    private $_attributes = null;
    protected $_auth = null;
    
    public function __construct(){
        @session_start();
        if(!$this->isGuest){
            $this->_attributes=$this->getState('__attributes');
        }
    }

    function __get($proName){
        if($this->_attributes&&array_key_exists($proName , $this->_attributes)){
            return $this->_attributes[$proName];
        }
        return parent::__get($proName);
    }
    
    
    //session 用户登录前缀
    public function getStateKeyPrefix()
	{
		if(self::$_keyPrefix!==null){
            return self::$_keyPrefix;
		}else{
            self::$_keyPrefix=md5('Ivy.'.get_class($this).$this->_attributes['id']?$this->_attributes['id']:'guest');
            return self::$_keyPrefix;
		}
			
	}
    
    //存储器 默认sssion方式
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
    
    /**
     * session 销毁
     * @return [type] [description]
     */
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
		$this->setId($user->id);
        $this->setState('__attributes',$user->attributes);
        $this->_attributes=$user->attributes;
        
	}
    public function logout()
	{
		$this->clearStates();
		$this->clearAuthCache();
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


	/**
	 * 权限检测快捷方式
	 * @return boolen
	 */
	public function checkAccess($route){
		if($this->isGuest) return false;
		$auth_list = $this->getAuthList();
		return $this->getAuth()->checkAccess($route,$auth_list);
	}

	public function getAuth() {
        if($this->_auth instanceof \rbac\AuthController){
            return $this->_auth;
        }else{
            $this->_auth = new \rbac\AuthController ();
            return $this->_auth;
        }
    }

    public function getAuthList(){
    	$prefix=$this->getStateKeyPrefix();
    	$list=\Ivy::app()->cache->get('auth_list_'.$prefix);
		if($list==null){
			$list = $this->getAuth()->getAuthList($this->getId());
			\Ivy::app()->cache->set('auth_list_'.$prefix,$list);
		}
		return $list;
    }
    /**
     * 权限缓存 销毁
     * @return [type] [description]
     */
    public function clearAuthCache()
	{
		$prefix=$this->getStateKeyPrefix();
		$list=\Ivy::app()->cache->delete('auth_list_'.$prefix);
	}




    
    
}