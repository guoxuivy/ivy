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
	 * 获取当前主机域名
	 * @return string 主机字符串
	 */
	public function getHostInfo(){
		return \Ivy::getHostInfo();
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