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

	//逻辑层池
	private $_logics=array();
	private $_view=null;

	public function __construct($route=NULL) {
		$this->attachBehavior($route,'route');//路由注入
		$this->init();
	}

	public function init() {}

	public function getDb() {
		return \Ivy::app()->getDb();
	}

    public function getRequest() {
        return \Ivy::request();
    }

    /**
     * 获取模版对象
     * @return Template
     */
	public function getView() {
		if(empty($this->_view)){
			$this->_view = new Template($this);
		}
		return $this->_view;
	}

	/**
	 * 获取逻辑层实例
	 * @return [type] [obj]
	 */
	public function logic($className) {
		//目录形式兼容
		$className = str_replace('/', '\\', $className);
		if(substr($className,0,1)=='\\'){
			$className = $className."Logic";
		}else{
			$className = ucfirst($className);
			$r = $this->getRouter();
			$module = $r['module'];
			$className = $module."\\".$className."Logic";
		}
		if (isset($this->_logics[$className])) {
			$logic = $this->_logics[$className];
		}else{
			$logic = new $className;
			$this->_logics[$className] = $logic;
		}
		return $logic;
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
     * ajax 返回
     * @param $code
     * @param string $message
     * @param array $data
     */
	protected function ajaxReturn($code, $message = '', $data = array()) {
        \Ivy::app()->closeProfile();
        header('Content-type: application/json');
		if (empty( $data )) {
			die ( json_encode ( array (
					'code' => $code,
					'msg' => $message 
			) ) );
		} else {
			die ( json_encode ( array (
					'code' => $code,
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
			$uri=$this->domain().$uri;
		}
		header('Location: '.$uri, true, 302);exit;
	}
	/**
	 * 获取当前主机域名
	 * @return string 主机字符串
	 */
	public function domain(){
		return \Ivy::request()->domain();
	}

	/**
	 * 判断是否为ajax请求
	 */
	public function getIsAjax(){
		return \Ivy::request()->isAjax();
	}

	/**
	 * 判断是否为post请求
	 */
	public function getIsPost(){
        return \Ivy::request()->isPost();
	}
	
}