<?php
namespace init;
use Ivy\core\CException;
class Step6Controller extends SController
{
	const DEFAULT_PASSWORD = '123456';
	
	protected $company_id;
	
	public $title;
	
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(6);
		\admin\CompanyInfo::model()->setStep(6);
	}
	
	public function indexAction() {
		$company_id = $this->company_id;
		//部门数组
		$deptArr = \CommonConfig::getDeptArr();
		//职位数组
		$positionArr = \CommonConfig::getPositionArr();
		//门店职位数组
		$storePositionArr = $positionArr['100'];
		//门店数组
		$storeArr = \admin\CompanyStore::model()->findAll("company_id='$company_id' AND status=1");
		$availablePosition = array(
			'3',
			'5',
			'8',
		);
		if(isset($_POST) && !empty($_POST)) {
			foreach($_POST['netname'] as $key => $val) {
				$dept_id = $key;
				foreach($val as $k => $netname) {
					$userModel = new \admin\CompanyUser();
					$sex = $_POST['sex'][$key][$k];
					$position_id = $_POST['position_id'][$key][$k];
					$user_name = $_POST['user_name'][$key][$k];
					$status = $_POST['status'][$key][$k];
					$userModel->company_id = $this->company_id;
					$userModel->dept_id = $dept_id;
					$userModel->position_id = $position_id;
					$userModel->netname = $netname;
					$userModel->user_name = $user_name;
					$userModel->password = md5(self::DEFAULT_PASSWORD);
					$userModel->sex = $sex;
					$userModel->status = $status;
					$userModel->create_time = time();
					$userModel->save();
				}
			}
			\admin\CompanyInfo::model()->setStep(7);
			$this->redirect('init/step7');
		}
		$this->view->assign(array(
			'deptArr' => $deptArr,
			'positionArr' => $positionArr,
			'storePositionArr' => $storePositionArr,
			'availablePosition' => $availablePosition,
			'storeArr' => $storeArr,
		))->display('index');
	}
}