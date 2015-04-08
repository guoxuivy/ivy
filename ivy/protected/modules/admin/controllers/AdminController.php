<?php
namespace admin;
use Ivy\core\CException;
class AdminController extends \SController
{
	//布局文件
	public $layout='/layouts/main';

	public function indexAction() {
		$this->view->assign(array())->display('index');
	}


	


	
}