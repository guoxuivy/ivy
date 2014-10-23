<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace admin; 
class IndexController extends \CController {
    /**
	 * 显示模版实例示例
	 */
	public function indexAction() {
	   
	    //var_dump(ArticleModel::model()->findByPk(3));
        return $this->view->assign('bug1','admin下面的大bug widget 导入测试')->render( 'index' );
	}
	
}
