<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class Controller extends CComponent {
    //路由对象
	protected $route = NULL;
    
	public function __construct($route=NULL) {
       //当前的路由对象
       $this->route = $route;
       $this->init();
	}

	/**
	 * 实例化处理器
	 */
	public function init() {
	}

    public function getDb() {
		return \Ivy::app()->getDb();
	}
    public function getView() {
		return new Template($this);
	}
	/**
	 * 所有action前执行
	 */
	public function actionBefore() {
	}

    /**
     * ajax 返回
     * @param  srting $statusCode [状态]
     * @param  string $message    [消息]
     * @param  array  $data       [数据]
     * @return json             [json返回值]
     */
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
	 * 格式化url
	 * $uri     admin/order/index
	 * $param   array("id"=>1)
	 * @return string
	 */
	public function url($uri="",$param=array()){
		if(strpos($uri,'/')===false){
			// 如 'list' 不包含分隔符 默认在当前控制器下寻址
			$r=$this->route->getRouter();
			$uri=$r['controller'].'/'.$uri;
			if($r['module']) $uri=$r['module'].'/'.$uri;
		}
        $uri = SITE_URL.'/index.php?r='.rtrim($uri);
        $param_arr = array_filter($param);
        if(!empty($param_arr)){
            foreach($param_arr as $k=>$v){
                $k=urlencode($k);
                $v=urlencode($v);
                $uri.="&{$k}={$v}";
            }
        }
		return $uri;
	}
    
   /**
    * 重定向方法 
    * $uri     admin/order/index
    * $param   array("id"=>1)
    */
    public function redirect($uri="",$param=array())
	{
        if(strpos($uri,'://')===false){
        	$uri = $this->url($uri,$param);
            $uri=$this->getHostInfo().$uri;
		}
        header('Location: '.$uri, true, 302);exit;
            
	}
	/**
	 * 获取当前主机
	 * @return string 主机字符串
	 */
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
	protected function getIsAjax(){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
			return true;
		}else{
			return false;			
		}
	}
    /**
	 * 判断是否为post请求
	 */
	protected function getIsPost(){
		if(isset($_POST) && $_SERVER['REQUEST_METHOD']=="POST"){
			return true;
		}else{
			return false;			
		}
	}
	
}