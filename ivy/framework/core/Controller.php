<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class Controller extends CComponent {
	//默认布局文件
	public $layout=NULL;

	public function __construct($route=NULL) {
		$this->attachBehavior($route);//路由方法注入
		$this->init();
	}

	public function init() {}

	public function getDb() {
		return \Ivy::app()->getDb();
	}
	/**
	 * 获取模版对象
	 * @return [type] [description]
	 */
	public function getView() {
		return new Template($this);
	}

	/**
	 * 所有action前自动执行
	 */
	public function actionBefore() {}
	/**
	 * 所有action 后自动执行
	 */
	public function actionAfter() {}

	/**
	 * 标准路由地址 
	 * 由route对象注入
	 */
	//public function url(){}

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
					'code' => $statusCode,
					'msg' => $message 
			) ) );
		} else {
			die ( json_encode ( array (
					'code' => $statusCode,
					'msg' => $message,
					'data' => $data 
			) ) );
		}
	}

	/**
	* 重定向方法 
	* $uri     admin/order/index
	* $param   array("id"=>1)
	*/
	public function redirect($uri="",$param=array()){
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
	public function getHostInfo(){
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

	/**
	 * https 判断
	 * @return [type] [description]
	 */
	public function getIsSecureConnection(){
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off');
	}

	/**
	 * 判断是否为ajax请求
	 */
	public function getIsAjax(){
		return \Ivy::isAjax();
	}
	
	/**
	 * 判断是否为post请求
	 */
	public function getIsPost(){
		if(isset($_POST) && $_SERVER['REQUEST_METHOD']=="POST"){
			return true;
		}else{
			return false;
		}
	}
	
}