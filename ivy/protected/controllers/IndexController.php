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
		$typeList = $this->db->find( 'article_cate' ); // 获取类型
        Ivy::app()->cache->set('guox',$typeList);
        //var_dump(Ivy::app()->cache->getConfigByKey('aqwewsd2132sed13'));die;
        $page=isset($_GET['p'])?(int)$_GET['p']:1;
		$article = $this->db->getPagener('article',$order = array('add_time' => 'DESC'),10,$page);
        $this->view->assign('article',$article)->display();
	}
    
   	/**
	 * widget 方式输出模板
	 */
	public function headerAction() {
        $this->view->assign('title','我是头部文件啊！我是头部文件啊！我是头部文件啊！我是头部文件啊！')
                    ->display( 'header' );
	}
    
    
    
    /**
	 * 点击访问单条article
	 * pjax测试 
	 */
	public function viewAction(){
		if (array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX']) { 
			echo $this->view->assign( 'article', ArticleModel::model()->findByPk($_GET['id']))->render();
		}else{
			$this->view->assign( 'article', ArticleModel::model()->findByPk($_GET['id']))->display();
		}
	}
	
}
