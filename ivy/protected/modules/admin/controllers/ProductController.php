<?php
namespace admin;
use Ivy\core\CException;
class ProductController extends \SController
{
	//布局文件
	public $layout='/layouts/main';

	public function indexAction() {
		$pager= \ProductInfo::model()->where(array('comp_id'=>$this->company['id']))->page(1,10)->getPagener();
		$this->view->assign(array('pager'=>$pager))->display();
	}


	/**
	 * 产品分类管理
	 * @return [type] [description]
	 */
	public function cateAction() {
		$topCate = \ProCate::getProductTopCate();
		$childCate=array();
		foreach ($topCate as $key => $value) {
			$childCate[$key]=\ProCate::model()->field('id,name')->findAll('fid='.$key.' and comp_id='.$this->company['id'].' and level=2 and type=2');
		}
		$this->view->assign(array(
			'topCate'=>$topCate,
			'childCate'=>$childCate,
		))->display();
	}

	public function addCateAction() {
		$cate = new \ProCate;
		$cate->attributes=$_POST;
		$cate->comp_id=$this->company['id'];
		$cate->level=2;
		$cate->type=2;
		$cate->save();
	}
	

	
}