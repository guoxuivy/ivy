<?php
/**
 * @Author: K
 * @Date:   2015-04-02 16:17:19
 * @Last Modified by:   K
 * @Last Modified time: 2015-04-02 17:52:21
 */
//项目管理
namespace admin;
use Ivy\core\CException;
class ProjectController extends \SController

{
	//布局文件
	public $layout='/layouts/main';

	public function indexAction() {
		$pager= \ProjectInfo::model()->page(1,10)->getPagener();
		$this->view->assign(array('pager'=>$pager))->display();
	}


	/**
	 * 项目分类管理
	 * @return [type] [description]
	 */
	public function cateAction() {
		$topCate = \ProCate::model()->field('id,name')->findAll("fid=0 and comp_id={$this->company['id']} and level=2 and type=1");
		$childCate=array();
		foreach ($topCate as $key => $value) {
			$childCate[$key]=\ProCate::model()->field('id,name')->findAll('fid='.$value['id'].' and comp_id='.$this->company['id'].' and level=2 and type=1');
		}
		$this->view->assign(array(
			'topCate'=>$topCate,
			'childCate'=>$childCate,
		))->display();
	}

	public function addCateAction() {
		$cate = new \ProCate;
		$cate->attributes=$_POST;
		$company_info=\Ivy::app()->user->getState('company_info');
		$cate->comp_id=$company_info['id'];
		if ($cate->fid>0) {
			$cate->level=2;
		}
		// $cate->level=2;
		$cate->type=1;
		$cate->save();
	}
	//添加商品
	public function createAction(){
		$model=new \ProjectInfo;
		if($model->comp_id!=$this->company['id']) {
			throw new CException('无效参数');
		}
		if (isset($_POST['data'])) {
			$model->saveData($_POST['data']);
		}
		$this->view->assign(array(
			'model'=>$model,
			))->display();
	}
	//编辑商品
	public function updateAction($id){
		$id=(int)$id;
		$model=new \ProjectInfo::model()->findByPk($id);
		if($model->comp_id!=$this->company['id']) {
			throw new CException('无效参数');
		}
		if (isset($_POST['data'])) {
			$model->saveData($_POST['data']);
		}
		$this->view->assign(array(
			'model'=>$model,
			))->display();
	}
}