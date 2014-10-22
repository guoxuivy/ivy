<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
abstract class Controller {
    //配置存储
	private $config = array ();
    //变量存储
	protected $data = array ();
    
	public function __construct($app) {
	   //application副本保存（router等参数实时化）
	   $this->data['app'] = clone $app;
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
	public function __get($name) {
		if (isset ( $this->data[$name] )) {
			return $this->data[$name];
		} else if ($name == 'view') {
			$this->data [$name] = new Template($this);
			return $this->data[$name];
		} else if (isset ( $this->config[$name] )) {
			return $this->config[$name];
		} else if ($name == 'db') {
		     return \Ivy::app()->getDb();
		} else {
			throw new CException ( '找不到' . $name );
		}
	}
    
 
	
	/**
	 * 判断是否为ajax请求
	 */
	protected function isXMLHttpRequest(){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
			return true;
		}else{
			return false;			
		}
	}
	
	protected function setSession($key,$val){
		session_start();
		$_SESSION[$key] = $val;
	}
	
	protected function getSession($key){
		session_start();
		if(isset($_SESSION[$key])){
			return $_SESSION[$key];
		}else{
			return false;
		}
	}
	
	protected function cleanSesstion($key){
		session_start();
		if(isset($_SESSION[$key])){
			unset($_SESSION[$key]);
		}
	}
	
	protected function destorySession(){
		session_start();
		session_unset();
		session_destroy();
	}
	
}
