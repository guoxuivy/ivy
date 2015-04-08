<?php
namespace init;
use Ivy\core\CException;
class Step3Controller extends SController
{
	public $title = '房间管理';
	
	protected $company_id;
	
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(3);
		\admin\CompanyInfo::model()->setStep(3);
	}
	
	public function indexAction() {
		$company_id = $this->company_id;
		$model = \admin\CompanyStore::model()->findAll("company_id='$company_id' AND status=1");
		if(isset($_POST) && !empty($_POST) && !empty($_POST['room_name'])) {
			foreach($_POST['room_name'] as $key => $val) {
				$store_id = $key;
				foreach($val as $k => $room_name) {
					$bed_name_str = $_POST['bed_name'][$key][$k];
					$bed_name_arr = explode(',', $bed_name_str);
					$roomModel = new \admin\CompanyRoom();
					$roomModel->store_id = $store_id;
					$roomModel->name = $room_name;
					$roomModel->create_time = time();
					$roomModel->save();
					$room_id = $roomModel->id;
					foreach($bed_name_arr as $bed_name) {
						$bedModel = new \admin\CompanyBed();
						$bedModel->room_id = $room_id;
						$bedModel->name = $bed_name;
						$bedModel->create_time = time();
						$bed_id = $bedModel->save();
					}
				}
			}
			$this->redirect('init/step4');
		}
		$this->view->assign(array(
			'model' => $model,
		))->display('index');
	}
}