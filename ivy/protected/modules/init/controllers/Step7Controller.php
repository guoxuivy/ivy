<?php
namespace init;
use Ivy\core\lib\UploadFile;
use Ivy\core\CException;
class Step7Controller extends SController
{
	protected $company_id;
	
	public $title;
	
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(7);
		\admin\CompanyInfo::model()->setStep(7);
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
		
		$model=\admin\CompanyProduct::model()->findAll("company_id={$this->company_id}  and status=1");
		$this->view->assign(array(
			'model'=>$model,
				))->display();
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
		//var_dump($data->sheets[0]['numRows']);die;
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
		//商品单位数组
		$unitArr = \CommonConfig::getProductUnit();
		//商品分类数组
		$levelArr = \CommonConfig::getProductLevel();
		//插入数据时出错的行
		$errorLineArr = array();
		@set_time_limit(0);
		foreach($data as $key => $val) {
			$productModel = new \admin\CompanyProduct();
			$productModel->company_id = $this->company_id;
			$product_level = ($level_key = array_search($val[1], $levelArr)) ? $level_key : 1;
			$product_name = $val[2];
			$price = empty($val[3]) ? 0 : $val[3];
			$unit = ($unit_key = array_search($val[4], $unitArr)) ? $unit_key : 1;
			$page_size = $val[5];
			$number = empty($val[6]) ? 1 : $val[6];
			$product_code = $val[7];
			$productModel->product_level = $product_level;
			$productModel->product_name = $product_name;
			$productModel->price = $price;
			$productModel->unit = $unit;
			$productModel->page_size = $page_size;
			$productModel->number = $number;
			$productModel->product_code = $product_code;
			$productModel->create_time = time();
			if($productModel->save()) {
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
		$file = SITE_URL . '/upload/product_template.xls';
		echo $file;
		exit;
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
		$model=\admin\CompanyProduct::model()->findByPk($id);
		if (isset($_POST['data'])) {
			if (empty($model)){
				$model=new \admin\CompanyProduct;
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
		$model=\admin\CompanyProduct::model()->findByPk($id);
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
		$model=new \admin\CompanyProduct;
		if ($model->deleteAll($this->company_id)) {
			$this->ajaxReturn('200','重置成功!');
		}
		$this->ajaxReturn('400','重置失败!');
		
	}
}