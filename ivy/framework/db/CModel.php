<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\db;
use Ivy\core;
abstract class CModel {
	//缓存时间30秒
	private static $cachetime=30;

	private static $_models=array();
	
	public $pages;//分页
	public $sort;//排序
	
	public function __construct(){
	}
	
	public function setPages($pages) {
		$this->pages = $pages;
	}
	public function getPages() {
		return $this->pages;
	}
	public function setSort($sort) {
		$this->sort = $sort;
	}
	public function getSort() {
		return $this->sort;
	}
	
	public function getDb(){
		return Ivy::app()->getDb();
	}
	
	public static function model($className=__CLASS__)
	{
		if(isset(self::$_models[$className]))
			return self::$_models[$className];
		else
		{
			$model=self::$_models[$className]=new $className(null);
			return $model;
		}
	}
}