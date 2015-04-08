<?php
namespace init;
use Ivy\core\CException;
class Step5Controller extends SController
{
	protected $company_id=1;
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(5);
		\admin\CompanyInfo::model()->setStep(5);
	}
	/**
	 * 列表页
	 * @return 页面
	 */
	public function indexAction() {
		$model=\admin\CompanySupplier::model()->findAll("company_id={$this->company_id}  and status=1");
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
		$model=\admin\CompanySupplier::model()->findByPk($id);
		if (isset($_POST['data'])) {
			if (empty($model)){
				$model=new \admin\CompanySupplier;
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
		$model=\admin\CompanySupplier::model()->findByPk($id);
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
		$model=new \admin\CompanySupplier;
		if ($model->deleteAll($this->company_id)) {
			$this->ajaxReturn('200','重置成功!');
		}
		$this->ajaxReturn('400','重置失败!');
		
	}
}