<?php
namespace init;
use Ivy\core\CException;
class Step1Controller extends SController
{
	protected $company_id;
	
	public $title = '部门管理';
	
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(1);

	}
	
	public function indexAction() {
		// 测试代码
		// $map['t.id'] = array(array('gt',1),array('lt',10));
		// $map['_logic'] = 'OR';
		// $map['info.company_name'] = array('neq','CCTV');
		// $sql = \admin\CompanyAccount::model()
		// ->field(array('info.`company_name`'=>'c_name'))
		// ->join('`company_info` info on t.`company_id` = info.`id`')
		// ->where($map)
		// ->limit('2')
		// ->order('id desc')
		// ->findAll();
		// var_dump(\admin\CompanyAccount::model()->lastSql);die;

		// $res = \admin\CompanyProject::model()->page(3,10)->getPagener();
		// var_dump(\admin\CompanyProject::model()->lastSql);die;
// 		$c = array (
// 			'dsn' => 'mysql:dbname=beauty_basis;host=192.168.1.202:13306',
// 			'user' => 'root',
// 			'password' => 'mysqladmin56',
// 		);
// $m_basis = \admin\CompanyAccount::model($c)->findAll();
// $m = \admin\CompanyAccount::model()->findAll();
// var_dump($m ,$m_basis);die;
// 
		//部门数组
		$deptArr = \CommonConfig::getDeptArr();
		//职位数组
		$positionArr = \CommonConfig::getPositionArr();
		//高级版的职位数组
		$seniorPositionArr = \CommonConfig::getSeniorPositionArr();
		
		//高级版才可用的职位（财务经理）
		$disabledArr = array(
			'6'
		);
		
		if(isset($_POST) && !empty($_POST)) {
			foreach($_POST['position'] as $key => $val) {
				foreach($val as $p_id) {
					$model = new \admin\CompanyPosition();
					$model->company_id = $this->company_id;
					$model->dept_id = $key;
					if($key == '100') { //门店
						$model->dept_id = 0;
					}
					$model->position_id = $p_id;
					$model->create_time = time();
					$model->save();
				}
			}
			\admin\CompanyInfo::model()->setStep(2);
			//TODO 同步更新公司初始化进行到了第几步
			$this->redirect('init/step2');
		}
		
		$this->view->assign(array(
			'deptArr' => $deptArr,
			'positionArr' => $positionArr,
			'disabledArr' => $disabledArr,
			'seniorPositionArr' => $seniorPositionArr,
		))->display('index');
	}
}