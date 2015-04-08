<?php
namespace init;
use Ivy\core\CException;
class Step2Controller extends SController
{
	protected $company_id=1;
	public function init() {
		parent::init();
		$company_info = \Ivy::app()->user->getState('company_info');
		$this->company_id = $company_info['id'];
		$this->title = \CommonConfig::getInitStep(2);
	}
	/**
	 * 列表页
	 * @return 页面
	 */
	public function indexAction() {
		$model=\admin\CompanyStore::model()->findAll("company_id={$this->company_id} and id>=100 and status=1");


		//die;
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
		$model=\admin\CompanyStore::model()->findByPk($id);
		if (isset($_POST['data'])) {
			if (empty($model)){
				$model=new \admin\CompanyStore;
				$model->create_time=time();
			}
			$model->attributes=$_POST['data'];
			$model->company_id=$this->company_id;
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
		$model=\admin\CompanyStore::model()->findByPk($id);
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
		$model=new \admin\CompanyStore;
		if ($model->deleteAll($this->company_id)) {
			$this->ajaxReturn('200','重置成功!');
		}
		$this->ajaxReturn('400','重置失败!');
		
	}
}