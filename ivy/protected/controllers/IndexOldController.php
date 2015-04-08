<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @since 1.0
 */
use \Ivy\core\Controller;
use \Ivy\core\CException;
use \Ivy\core\lib\Image;
/**
 * 系统登录注册控制器
 */
class IndexOldController extends Controller {
	
	private $_requestUri;
	//布局文件
	public $layout=false;

	/**
	 * 默认页面，跳转到登录界面
	 */
	public function indexAction() {
		if(Ivy::app()->user->getIsGuest()) {
			$this->redirect('login');
		}
		else {
			$return_url = Ivy::app()->user->getReturnUrl();
			if($return_url)
				$this->redirect($return_url);
			$this->redirect('login');
		}
	}

	/**
	 * 转入业务系统
	 * @return [type] [description]
	 */
	static public function toBeauty($company_name,$user_name) {
		return;
		$config = \Ivy::app()->config;
		$uri=$config['beauty_platform_uri']."?r=site/PTLogin&by=pt&company_name=".$company_name."&user_name=".$user_name;
		header('Location: '.$uri, true, 302);exit;
	}

	/**
	 * 业务系统登录验证
	 * @param  [type] $database    [description]
	 * @param  [type] $user_name   [description]
	 * @param  [type] $md5password [description]
	 * @return [boolen]              
	 */
	public function beautyLogin($database,$user_name,$md5password){
		$db_connect=mysql_connect('192.168.1.202:13306','root','mysqladmin56') or die("platform connect error");
		//选择一个需要操作的数据库
		mysql_select_db($database,$db_connect);
		//执行MySQL语句
		$result=mysql_query("SELECT * FROM `employ_user` WHERE user_name = '{$user_name}' AND password = '{$md5password}' AND user_state = '1'  ");
		//提取数据
		$row=mysql_fetch_row($result);
		if($row)
			return true;
		return false;
	}
	
	/**
	 * 登录
	 */
	public function loginAction() {
		if(isset($_POST) && !empty($_POST)) {
			$verify = Ivy::app()->user->getState('verify');
			$company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
			$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
			$password = isset($_POST['password']) ? trim($_POST['password']) : '';
			$verifyCode = isset($_POST['verifyCode']) ? trim($_POST['verifyCode']) : '';
			$companyModel = \admin\CompanyInfo::model()->find("company_name='$company_name'");
			$md5password = md5($password);
			$userModel = \admin\CompanyUser::model()->find("company_id={$companyModel->id} AND user_name='$user_name' AND password='$md5password' AND status=1");
			if(empty($userModel) && $companyModel->is_init==1){
				self::toBeauty($company_name,$user_name);//切入业务系统
			}
			// if(\Ivy::app()->user->is_platform==1){
			// 	//平台账户进入管理员界面
			// }else{
			// 	//非平台账户
			// }
			//$userModel = \admin\CompanyUser::model()->find("company_id={$companyModel->id} AND user_name='$user_name' AND password='$md5password' AND status=1");
			$company_info = $companyModel->getAttributes();
			Ivy::app()->user->setState('company_info', $company_info);
			Ivy::app()->user->login($userModel);
			if($userModel->dept_id == 0) { //管理员登录
				$is_init = $companyModel->is_init;
				if($is_init == 1) { //初始化完成
					$return_url = Ivy::app()->user->getReturnUrl();
					if($return_url) {
						$this->redirect($return_url);
					}
					else {
						
					}
				}
				else {
					$init_step = $companyModel->init_step;
					$this->redirect('init/step'.$init_step);
				}
			}
			else { //其他员工登录
				$return_url = Ivy::app()->user->getReturnUrl();
				if($return_url) {
					$this->redirect($return_url);
				}
				else {
				
				}
			}
		}
		
		$this->view->assign(array(
			
		))->display('login');
	}
	
	/**
	 * 登录验证
	 * @throws CException
	 */
	public function validateAction() {
		if(!$this->getIsAjax()) {
			throw new CException('非法访问');
		}
		$verify = Ivy::app()->user->getState('verify');
		$company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
		$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
		$password = isset($_POST['password']) ? trim($_POST['password']) : '';
		$verifyCode = isset($_POST['verifyCode']) ? trim($_POST['verifyCode']) : '';
		$companyModel = \admin\CompanyInfo::model()->find("company_name='$company_name'");
		if(empty($companyModel)) {
			$this->ajaxReturn('500', '公司名不正确');
		}
		if($verify != md5($verifyCode)) {
			$this->ajaxReturn('500', '验证码不正确');
		}
		$md5password = md5($password);

		if($companyModel->is_init==1){
			//业务系统用户验证
			$res = $this->beautyLogin($companyModel->database,$user_name,$md5password);
			if(!$res)
				$this->ajaxReturn('500', '账户名或密码不正确');
		}else{
			$userModel = \admin\CompanyUser::model()->find("company_id={$companyModel->id} AND user_name='$user_name' AND password='$md5password' AND status=1");
			if(empty($userModel)) 
				$this->ajaxReturn('500', '账户名或密码不正确');
		}
		$this->ajaxReturn('200', '验证成功');
	}
	
	public function verifyAction(){
		$w = 50;
		$h = 24;
		Image::buildImageVerify(4, 1, 'png', $w, $h);
	}
	
	/**
	 * 登出
	 */
	public function logoutAction() {
		Ivy::app()->user->logout();
		$this->redirect('login');
	}
	
	/**
	 * 注册第一步
	 */
	public function register1Action() {
		if(isset($_POST) && !empty($_POST)) {
			$model = new CompanyInfo();
			$model->company_name = trim($_POST['company_name']);
			$model->pca_province = $_POST['pca_province'];
			$model->pca_city = $_POST['pca_city'];
			$model->address = trim($_POST['address']);
			$model->store_num = intval($_POST['store_num']);
			$model->contact_name = trim($_POST['contact_name']);
			$model->position = trim($_POST['position']);
			$model->phone = trim($_POST['phone']);
			$model->email = trim($_POST['email']);
			$model->create_time = time();
			$result = $model->save();
			if($result) {
				$this->redirect('register2');
			}
		}
		$this->view->assign()->display('register1');
	}
	
	/**
	 * 注册第一步验证
	 */
	public function validate1Action() {
		if(!$this->getIsAjax()) {
			throw new CException('非法访问');
		}
		$company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
		if(empty($company_name)) {
			$this->ajaxReturn('500', '公司名不能为空');
		}
		$ifCompanyExists = \admin\CompanyInfo::model()->find("company_name='$company_name'");
		if(!empty($ifCompanyExists)) {
			$this->ajaxReturn('500', '公司名已注册');
		}
		$this->ajaxReturn('200', '验证成功');
	}
	
	/**
	 * 注册第二步
	 */
	public function register2Action() {
		$this->view->assign()->display('register2');
	}
	
	/**
	 * 注册第三步
	 * @throws CException
	 */
	public function register3Action() {
		$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
		$company_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		if(empty($token) || empty($company_id)) {
			throw new CException('非法链接');
		}
		$model = \admin\CompanyInfo::model()->findByPk($company_id);
		if(empty($model)) {
			throw new CException('无法找到公司信息');
		}
		$now = time();
		if($model->token != $token) {
			throw new CException('非法链接');
		}
		if($now > $model->token_endtime) {
			throw new CException('注册链接已超时');
		}
		if(isset($_POST) && !empty($_POST) && $this->getIsAjax()) {
			$account_name = isset($_POST['account_name']) ? trim($_POST['account_name']) : '';
			$password = isset($_POST['password']) ? trim($_POST['password']) : '';
			$password2 = isset($_POST['password2']) ? trim($_POST['password2']) : '';
			$accountModel = new \admin\CompanyAccount();
			$accountModel->company_id = $company_id;
			$accountModel->account_name = $account_name;
			$accountModel->password = md5($password);
			$accountModel->create_time = time();
			$ifExistsCompany = \admin\CompanyAccount::model()->find("company_id='$company_id'");
			$ifExistsAccount = \admin\CompanyAccount::model()->find("account_name='$account_name'");
			if(!empty($ifExistsCompany)) {
				$this->ajaxReturn('500', '该公司账号已注册');
			}
			if(!empty($ifExistsAccount)) {
				$this->ajaxReturn('500', '管理员账号已存在');
			}
			if(strlen($account_name) < 6) {
				$this->ajaxReturn('500', '管理员账号不得少于6位');
			}
			if(strlen($password) < 6) {
				$this->ajaxReturn('500', '密码不得少于6位');
			}
			if($password != $password2) {
				$this->ajaxReturn('500', '两次输入的密码不一致');
			}
			if($accountModel->save()) {
				//修改公司信息，状态改为激活，初始化第一步开始
				$model->status = 2;
				$model->active_time = time();
				$model->init_step = 1;
				$model->save();
				
				//将公司管理员账号信息插入到user表中
				$userModel = new \admin\CompanyUser();
				$userModel->company_id = $accountModel->company_id;
				$userModel->dept_id = 0;
				$userModel->position_id = 0;
				$userModel->netname = '管理员';
				$userModel->user_name = $accountModel->account_name;
				$userModel->password = $accountModel->password;
				$userModel->create_time = $accountModel->create_time;
				$userModel->save();
				
				$this->ajaxReturn('200', '注册成功');
			}
			else {
				$this->ajaxReturn('500', '注册失败');
			}
		}
		$this->view->assign(array(
			'model' => $model,
		))->display('register3');
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
	
	
    
}