<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
class Controller {
	//变量存储
	protected $data = array ();
	public function __construct($route) {
       //私有的路由对象
       $this->data['route'] = $route;
       $this->init();
	}

	public function init() { }

    /**
     * 搜索data
     **/
    public function __get($name) {
		if (method_exists($this,$name)) {
			return $this->$name();
		} else if (isset ( $this->data[$name] )) {
			return $this->data[$name];
		} else if ($name == 'view') {
			return new Template($this);
		} else if ($name == 'db') {
            return \Ivy::app()->getDb();
		} else {
			throw new CException ( '找不到' . $name );
		}
	}
	function __set($proName,$value){
		$method="set".ucfirst($proName);
		if(method_exists($this,$method)){
			return $this->$method($value);
		}elseif(property_exists($this,$proName)){
			return $this->$proName=$value;
		}else{
			return $this->data[$proName]=$value;
		}
	}
    
	protected function ajaxReturn($statusCode, $message, $data = array()) {
		if (empty ( $data )) {
			die ( json_encode ( array (
					'statusCode' => $statusCode,
					'message' => $message 
			) ) );
		} else {
			die ( json_encode ( array (
					'statusCode' => $statusCode,
					'message' => $message,
					'data' => $data 
			) ) );
		}
	}
    
   /**
    * 重定向方法 
    * $uri     admin/order/index
    * $param   array("id"=>1)
    */
    public function redirect($uri="",$param=array())
	{
        if(strpos($uri,'://')===false){
            //站内转跳
            $uri = SITE_URL.'/index.php/?r='.rtrim($uri);
            $param_arr = array_filter($param);
            if(!empty($param_arr)){
                foreach($param_arr as $k=>$v){
                    $k=urlencode($k);
                    $v=urlencode($v);
                    $uri.="&{$k}={$v}";
                }
            }
            $uri=$this->getHostInfo().$uri;
		}
        header('Location: '.$uri, true, 302);exit;
            
	}
    public function getHostInfo()
	{
	   if($secure=$this->getIsSecureConnection())
			$http='https';
		else
			$http='http';
		if(isset($_SERVER['HTTP_HOST']))
			$hostInfo=$http.'://'.$_SERVER['HTTP_HOST'];
		else
		{
			$hostInfo=$http.'://'.$_SERVER['SERVER_NAME'];
			$port=isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
			if($port!==80)
				$hostInfo.=':'.$port;
		}
        return $hostInfo;
	}
    
    public function getIsSecureConnection()
	{
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off');
	}


	
	/**
	 * 判断是否为ajax请求
	 */
	protected function isAjax(){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
			return true;
		}else{
			return false;			
		}
	}
    /**
	 * 判断是否为post请求
	 */
	protected function isPost(){
		if(isset($_POST) && $_SERVER['REQUEST_METHOD']=="POST"){
			return true;
		}else{
			return false;			
		}
	}
	
}
