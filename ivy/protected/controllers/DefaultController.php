<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
class DefaultController extends \CController {
	
	/**
	 * 显示模版实例示例
	 */
	public function indexAction(){
        $this->view->assign('bug','defaul的我是大bug我是大bug我是大bug我是大bug我是大bug')
					 ->display( 'default/index' );
	}
	
	
}
