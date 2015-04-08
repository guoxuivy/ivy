<?php
namespace pos;
use Ivy\core\CException;
class SaleController extends \SController
{
	//布局文件
	public $layout='/layouts/main';

	public function indexAction() {
		$this->view->assign(array())->display('index');
	}


	


	
}
