<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
class ArticleModel extends \CModel
{
    /**
     * 必须方法一
     **/
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    /**
     * 必须方法二
     **/
    public function tableName()
	{
		return 'article';
	}
	
}