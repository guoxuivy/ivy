<?php
namespace init;
use Ivy\core\lib\UploadFile;
use Ivy\core\CException;
class Step8Controller extends SController
{
	protected $company_id=1;
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(8);
		\admin\CompanyInfo::model()->setStep(8);
	}
	/**
	 * 列表页
	 * @return 页面
	 */
	public function indexAction() {
		if($this->getIsAjax() && isset($_REQUEST['method'])) {
			$method = $_REQUEST['method'];
			if(method_exists($this, $method)) {
				$this->$method();
				exit;
			}
		}
		
		$model=\admin\CompanyProject::model()->findAll("company_id={$this->company_id}  and status=1");
		$this->view->assign(array(
			'model'=>$model,
				))->display();
	}
	/**
	 * 编辑,新建
	 * @return 
	 */
	public function updateAction()
	{
		 if(!$this->getIsAjax())
		 	throw new CException('非法访问!');
		 $id=(int)$_REQUEST['id'];
		$model=\admin\CompanyProject::model()->findByPk($id);
		if (isset($_POST['data'])) {
			if (empty($model)){
				$model=new \admin\CompanyProject;
				$model->create_time=time();
			}
			$model->attributes=$_POST['data'];
			$model->company_id=$this->company_id;
			$model->create_time=time();
			if ($model->save()) {
				$this->ajaxReturn('200','保存成功!');
			}
			$this->ajaxReturn('400','保存失败!');
		}
		if(!empty($model) && $model->company_id!=$this->company_id)
			throw new CException('非法访问!');
		$this->view->assign(array(
			'model'=>$model,
				))->display();
	}
	/**
	 * 删除
	 * @return 
	 */
	public function deleteAction()
	{
		if(!$this->getIsAjax())
		 	throw new CException('非法访问!');
		$id=(int)$_REQUEST['id'];
		$model=\admin\CompanyProject::model()->findByPk($id);
		if($model->company_id!=$this->company_id)
			$this->ajaxReturn('400','非法访问!');
		$model->status=-1;
		if ($model->save()) {
			$this->ajaxReturn('200','删除成功!');
		}
		$this->ajaxReturn('400','删除失败!');
		
	}
	/**
	 * 重置
	 * @return 
	 */
	public function deleteAllAction()
	{
		if(!$this->getIsAjax())
		 	throw new CException('非法访问!');
		$model=new \admin\CompanyProject;
		if ($model->deleteAll($this->company_id)) {
			$this->ajaxReturn('200','重置成功!');
		}
		$this->ajaxReturn('400','重置失败!');
		
	}
	/**
	 * 全部重置返回第一步
	 * @return 
	 */
	public function backAllAction()
	{
		if(!$this->getIsAjax())
		 	throw new CException('非法访问!');
		$model=\admin\CompanyInfo::model()->findByPk($this->company_id);
		$model->init_step=1;
		if ($model->is_init==1)
			$this->ajaxReturn('400','全部重置失败!');
		if ($model->save()) {
			\admin\CompanyProject::model()->deleteAll($this->company_id);//8
			\admin\CompanyProduct::model()->deleteAll($this->company_id);//7
			\admin\CompanyProject::model()->deleteAll($this->company_id);//6
			\admin\CompanyUser::model()->deleteAll($this->company_id);//5
			\admin\CompanyMemberLevel::model()->deleteAll($this->company_id);//4
			\admin\CompanyRoom::model()->deleteAll($this->company_id);//3
			\admin\CompanyStore::model()->deleteAll($this->company_id);//2
			\admin\CompanyPosition::model()->deleteAll($this->company_id);//1
		}
		if ($model->deleteAll($this->company_id)) {
			$this->ajaxReturn('200','全部重置成功!');
		}
		$this->ajaxReturn('400','全部重置失败!');
		
	}
	public function uploadAction() {
		if(!$this->getIsAjax())
			throw new CException('非法访问!');
		$config = array(
			'allowExts' => array('xls'),
		);
		$uploadFile = new UploadFile($config);
		$savePath = __ROOT__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
		if($uploadFile->upload($savePath)) {
			$fileinfo = $uploadFile->getUploadFileInfo();
			$saveFile = $fileinfo[0]['savepath'] . $fileinfo[0]['savename'];
			$result = $this->insertData($saveFile);
			@unlink($saveFile);
			if($result) {
				$this->ajaxReturn('200', '上传成功', $result);
			}
		}
		else {
			$error = $uploadFile->getErrorMsg();
			$this->ajaxReturn('500', '上传失败', $error);
		}
	}
	
	/**
	 * 读取Excel，分析数据
	 * @param string $file_name 导入数据用的Excel文件位置
	 * @return array|boolean
	 */
	protected function readExcel($file_name) {
		if(!isset($file_name)) {
			return false;
		}
        \Ivy::importExt('excel/Spreadsheet_Excel_Reader');
		$data = new \Spreadsheet_Excel_Reader();
		// Set output Encoding.
		$data->setOutputEncoding('UTF-8');
		$data->read($file_name);
	
		$excel_data = array();
		for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
			$row_data = array();
			for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
				$row_data[] = $data->sheets[0]['cells'][$i][$j];
			}
			$excel_data[] = $row_data;
		}
		return $excel_data;
	}
	
	/**
	 * 批量插入数据
	 * @param string $file_name 导入数据用的Excel文件位置
	 * @return array
	 */
	protected function insertData($file_name) {
		$data = $this->readExcel($file_name);
		array_shift($data);
		$count = count($data);
		$i = 0;
		//项目分类数组
		$levelArr = \CommonConfig::getProjectLevel();
		//插入数据时出错的行
		$errorLineArr = array();
		@set_time_limit(0);
		foreach($data as $key => $val) {
			$projectModel = new \admin\CompanyProject();
			
			$project_name = $val[1];
			$project_level = ($level_key = array_search($val[2], $levelArr)) ? $level_key : 1;
			$supplier_id = (empty($val[3]) || !is_numeric($val[3])) ? 1 : $val[3];
			$price = empty($val[4]) ? 0 : $val[4];
			$num = empty($val[5]) ? 1 : $val[5];
			$practice_price = empty($val[6]) ? 0 : $val[6];
			$zs_practice_price = empty($val[7]) ? 0 : $val[7];
			$project_code = $val[8];
			
			$projectModel->company_id = $this->company_id;
			$projectModel->project_name = $project_name;
			$projectModel->project_level = $project_level;
			$projectModel->supplier_id = $supplier_id;
			$projectModel->price = $price;
			$projectModel->num = $num;
			$projectModel->practice_price = $practice_price;
			$projectModel->zs_practice_price = $zs_practice_price;
			$projectModel->project_code = $project_code;
			$projectModel->create_time = time();
			if($projectModel->save()) {
				$i++;
			}
			else {
				$errorLineArr[] = $val[0];
			}
		}
		$arr = array(
			'total' => $count,
			'success' => $i,
			'error' => $errorLineArr,
		);
		return $arr;
	}
	
	protected function getDownloadFile() {
		$file = SITE_URL . '/upload/project_template.xls';
		echo $file;
		exit;
	}
	/**
	 * 完成
	 * @return 
	 */
	public function doneAction()
	{
		 if(!$this->getIsAjax())
		   	throw new CException('非法访问!');
		$model=\admin\CompanyInfo::model()->findByPk($this->company_id);
		if($model->init_step==8 && $model->is_init==0)
		{
			$db=$this->getDb();
			$sql = file_get_contents(__ROOT__. '/data/install.sql');
			$sql = str_replace("`beauty_platform`", $model->database, $sql);
			//公司
			$sql.="INSERT INTO `employ_company` (`id`,`company_name`, `address`, `tel`, `note`) VALUES (1,'{$model->company_name}', '{$model->address}', '{$model->phone}', '');";
			//门店
			$sql.="INSERT INTO `employ_dept` (`id`,`dept_name`, `company_id`)  (SELECT `id`,`name`,1 FROM `beauty_admin`.`company_store` WHERE company_id={$model->id} AND status=1);";
			//房间
			$sql.="INSERT INTO `sys_room_store` (`id`,`code`,`name`, `dept_id`)  (SELECT t.`id`,t.`name`,t.`name`,t.`store_id`  FROM `beauty_admin`.`company_room` t LEFT JOIN `beauty_admin`.`company_store` t2 on t.store_id=t2.id WHERE t2.company_id={$model->id} AND t.status=1);";
			//会员卡
			$sql.="INSERT INTO `sys_member_level` (`level`, `level_name`, `company_id`,`min_amount`) (SELECT `level`, `level_name`,1,'' FROM `beauty_admin`.`company_member_level` WHERE company_id={$model->id} AND status=1);";
			//供应商
			$sql.="INSERT INTO `invo_product_supplier` (`user_name`, `password`, `supplier`, `company_id`) (SELECT `name`,'".md5('123456')."', `name`,1 FROM `beauty_admin`.`company_supplier` WHERE company_id={$model->id} AND status=1);";
			//员工
			$sql.="INSERT INTO `employ_user` (`number_id`, `user_name`, `netname`, `password`, `idcard`, `phone`, `sex`, `marriage`, `blood`, `email`, `company_id`, `dept_id`, `position_id`, `addtime`, `ticket_month`) 
								(SELECT concat(CURDATE()+0,LEFT(MD5(RAND()),5)),`user_name`, `netname`, `password`,'-','-', `sex`,'','','',1, `dept_id`, `position_id`,UNIX_TIMESTAMP(),1 FROM `beauty_admin`.`company_user` WHERE company_id={$model->id} AND status=1);";
			//产品商品
			$sql.="INSERT INTO `invo_product_info` (`level_id`, `type`, `ext_type`, `product_name`, `product_code`, `price`, `page_size`, `unit`, `number`, `warning_a`, `supplement_num_a`, `supplier`, `company_id`)
			 (SELECT `product_level`,`product_level`,1,`product_name`,`product_code`,`price`,`page_size`, `unit`, `number`,0,0,0,1 FROM `beauty_admin`.`company_product` WHERE company_id={$model->id} AND status=1);";
			//项目
			
			$sql.="INSERT INTO `sys_project` (`project_name`, `category_id`, `type`, `price`, `course_number`, `rel_products`, `rel_products_name`, `code`, `practice_price`, `zs_practice_price`, `supplier_id`)
			 (SELECT `project_name`,`project_level`,`project_level`,`price`,`num`,'','',`project_code`, `practice_price`, `zs_practice_price`, `supplier_id` FROM `beauty_admin`.`company_project` WHERE company_id={$model->id} AND status=1);";
			$sql.="UPDATE `beauty_admin`.`company_info` SET `is_init`=1, `complete_time`=UNIX_TIMESTAMP() WHERE  `id`={$model->id};";
			try {
				$res =$db->exec($sql);
				$this->ajaxReturn('200','初始化成功!');
			} catch (CException $e) {
				$this->ajaxReturn('200',$e);
			}	
		}
		$this->ajaxReturn('400','初始化失败!');
	}
}