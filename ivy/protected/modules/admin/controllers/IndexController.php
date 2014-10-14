<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace admin; 
use Ivy\core\BaseController;
class IndexController extends BaseController {
    /**
	 * 显示模版实例示例
	 */
	public function indexAction() {
        return $this->view->assign('bug1','admin下面的大bug')->render( 'index' );
	}
	
}
