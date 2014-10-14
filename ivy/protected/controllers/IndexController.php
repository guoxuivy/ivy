<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
use Ivy\core\BaseController,Ivy\db\Where;
class IndexController extends BaseController {
	
	/**
	 * 显示模版实例示例
	 */
	public function indexAction() {
		$typeList = $this->db->getData ( 'type' ); // 获取类型
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
		$where = new Where;
		$where->eqTo('id', $_GET['id']);
		$article = $this->db->getDataOne('article',$where);
		$this->view->assign ( 'article', $article )->display();
	}
    
	
	
}
