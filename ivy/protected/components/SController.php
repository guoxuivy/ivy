<?php
use Ivy\core\Controller;
use Ivy\core\CException;
/**
 * 系统控制器基类
 * 登录后相关操作使用
 */
class SController extends Controller
{
	//布局文件
	public $layout='/layouts/main';
	
	protected $title='美业云平台'; //页面标题

	protected $company=null; //登录用户公司信息
	
	public function init() {

		//判断是否登录
		if(\Ivy::app()->user->getIsGuest()) {
			$hostInfo = $this->getHostInfo();
			$return_url = $hostInfo . $this->getRequestUri();
			\Ivy::app()->user->setReturnUrl($return_url);
			$this->redirect('index/login');
		}else{
			
		}
		
	}
	

	
	/**
	 * 获取当前访问路径
	 * @return string
	 */
	public function getRequestUri()
	{
		$_requestUri=null;
		
		if(isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
			$_requestUri=$_SERVER['HTTP_X_REWRITE_URL'];
		elseif(isset($_SERVER['REQUEST_URI']))
		{
			$_requestUri=$_SERVER['REQUEST_URI'];
			if(!empty($_SERVER['HTTP_HOST']))
			{
				if(strpos($_requestUri,$_SERVER['HTTP_HOST'])!==false)
					$_requestUri=preg_replace('/^\w+:\/\/[^\/]+/','',$_requestUri);
			}
			else
				$_requestUri=preg_replace('/^(http|https):\/\/[^\/]+/i','',$_requestUri);
		}
		elseif(isset($_SERVER['ORIG_PATH_INFO']))  // IIS 5.0 CGI
		{
			$_requestUri=$_SERVER['ORIG_PATH_INFO'];
			if(!empty($_SERVER['QUERY_STRING']))
				$_requestUri.='?'.$_SERVER['QUERY_STRING'];
		}
	
		return $_requestUri;
	}
	

	public function actionBefore() {
		$this->company=\Ivy::app()->user->getState('company_info');
	}
	
}