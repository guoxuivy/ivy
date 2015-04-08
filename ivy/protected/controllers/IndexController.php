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
class IndexController extends Controller {
	
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
			$this->redirect('admin/admin');
		}
	}

	
	
	/**
	 * 登录
	 */
	public function loginAction() {
		if(isset($_POST) && !empty($_POST)) {
			Ivy::app()->user->checkToken();
			$verify = Ivy::app()->user->getState('verify');
			//$company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
			$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
			$password = isset($_POST['password']) ? trim($_POST['password']) : '';
			$verifyCode = isset($_POST['verifyCode']) ? trim($_POST['verifyCode']) : '';
			//$companyModel = \admin\CompanyInfo::model()->find("company_name='$company_name'");
			$md5password = md5($password);
			$userModel = \EmployUser::model()->find("user_name='$user_name' AND password='$md5password' AND status=1");

			if($userModel){
				Ivy::app()->user->login($userModel);
				$companyModel = \Company::model()->findByPk($userModel->comp_id);
				$company_info = $companyModel->getAttributes();
				Ivy::app()->user->setState('company_info', $company_info);
				$return_url = Ivy::app()->user->getReturnUrl();
				if($return_url) {
					$this->redirect($return_url);
				}else{
					$this->redirect('admin/admin');
				}
			}
			
		}
		
		$this->view->display('login');
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
		//$company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
		$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
		$password = isset($_POST['password']) ? trim($_POST['password']) : '';
		$verifyCode = isset($_POST['verifyCode']) ? trim($_POST['verifyCode']) : '';
		//$companyModel = \admin\CompanyInfo::model()->find("company_name='$company_name'");
		if(empty($companyModel)) {
			//$this->ajaxReturn('500', '公司名不正确');
		}
		if($verify != md5($verifyCode)) {
			$this->ajaxReturn('500', '验证码不正确');
		}
		$md5password = md5($password);

		$userModel = \EmployUser::model()->find("user_name='$user_name' AND password='$md5password' AND status=1");
		if(empty($userModel)) 
			$this->ajaxReturn('500', '账户名或密码不正确');
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
	

	
	
    
}