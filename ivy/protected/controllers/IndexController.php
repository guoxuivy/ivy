<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
class IndexController extends \CController {
	
	/**
	 * 显示模版实例示例
	 */
	public function indexAction() {
	    $r = ArticleModel::model()->findByPk(2);
		$typeList = $this->db->find( 'type' ); // 获取类型
		$article = $this->db->getPagener('article',$order = array('add_time' => 'DESC'),10,1);

        $this->view->assign('article',$article)->display();
	}
    
   	/**
	 * widget 方式输出模板
	 */
	public function headerAction() {
		return $this->view
                    ->assign('title','我是头部文件啊！我是头部文件啊！我是头部文件啊！我是头部文件啊！')
                    ->render( 'header' );
	}
    
    
    
    /**
	 * 点击访问单条article
	 */
	public function viewAction(){
		$this->view->assign ( 'article', ArticleModel::model()->findByPk($_GET['id']))->display();
	}
    
	
	
}
