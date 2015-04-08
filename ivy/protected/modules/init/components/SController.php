<?php
namespace init;
use Ivy\core\Controller;
use Ivy\core\CException;
class SController extends Controller
{
	protected $themePath; //主题路径
	//布局文件
	public $layout='/layouts/init';
	
	protected $title; //页面标题
	
	public $route_arr; //route路径的数组（array('module'=>'','controller'=>'','action'=>'')）
	
	private $_requestUri; //当前访问路径
	
	public function init() {
		$this->themePath = $this->view->basePath('public');
		$this->route_arr = $this->route->getRouter();

		//判断是否登录
		if(\Ivy::app()->user->getIsGuest()) {
			$hostInfo = $this->getHostInfo();
			$return_url = $hostInfo . $this->getRequestUri();
			\Ivy::app()->user->setReturnUrl($return_url);
			$this->redirect('index/login');
		}else{
			$new_db=array (
				'dsn' => 'mysql:dbname=beauty_basis;host=192.168.1.202:13306',
				'user' => 'root',
				'password' => 'mysqladmin56',
			);
			//如果已经登录切换到业务系统数据库
			//\Ivy::app()->C('db_pdo',$new_db);
		}
		
		
	}
	
	/**
	 * 创建本站内URL地址
	 * @param string $uri
	 * @param array $param
	 * @param boolean $absolute 是否是绝对地址
	 * @return string
	 */
	public function createUrl($uri, $param = array(), $absolute = true) {
		if(strpos($uri,'://')===false){
			$uri = SITE_URL.'/index.php?r='.rtrim($uri);
			$param_arr = array_filter($param);
			if(!empty($param_arr)){
				foreach($param_arr as $k=>$v){
					$k=urlencode($k);
					$v=urlencode($v);
					$uri.="&{$k}={$v}";
				}
			}
			if($absolute) {
				$uri = $this->getHostInfo() . $uri;
			}
		}
		return $uri;
	}
	
	/**
	 * 获取当前访问路径
	 * @return string
	 */
	public function getRequestUri()
	{
		if($this->_requestUri===null)
		{
			if(isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
				$this->_requestUri=$_SERVER['HTTP_X_REWRITE_URL'];
			elseif(isset($_SERVER['REQUEST_URI']))
			{
				$this->_requestUri=$_SERVER['REQUEST_URI'];
				if(!empty($_SERVER['HTTP_HOST']))
				{
					if(strpos($this->_requestUri,$_SERVER['HTTP_HOST'])!==false)
						$this->_requestUri=preg_replace('/^\w+:\/\/[^\/]+/','',$this->_requestUri);
				}
				else
					$this->_requestUri=preg_replace('/^(http|https):\/\/[^\/]+/i','',$this->_requestUri);
			}
			elseif(isset($_SERVER['ORIG_PATH_INFO']))  // IIS 5.0 CGI
			{
				$this->_requestUri=$_SERVER['ORIG_PATH_INFO'];
				if(!empty($_SERVER['QUERY_STRING']))
					$this->_requestUri.='?'.$_SERVER['QUERY_STRING'];
			}
		}
	
		return $this->_requestUri;
	}
	
	/**
	 * 页面加载之前
	 * 1.判断是否登录
	 * 2.渲染页面的头文件
	 * 3.判断该公司进行到第几步，并跳转到那一步
	 * @see \Ivy\core\Controller::actionBefore()
	 */
	public function actionBefore() {
		parent::actionBefore();
		//判断该公司进行到第几步，并跳转到那一步
		$company_info = \Ivy::app()->user->getState('company_info');
		$company_info_model = \admin\CompanyInfo::model()->findByPk($company_info['id']);
		$is_init = $company_info_model->is_init;
		$init_step = $company_info_model->init_step;
		$step_controller = 'step' . $init_step;
		if($is_init == 0 && strtolower($this->route_arr['module']) == 'init' && strtolower($this->route_arr['controller']) != $step_controller) { 
			$this->redirect('init/'.$step_controller);
		}elseif($is_init==1){
			//切入业务系统
			\IndexController::toBeauty($company_info['company_name'],\Ivy::app()->user->user_name);
		}else{
			//throw new CException('非法访问');
		}
		
	}
	
}