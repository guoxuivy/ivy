<?php
/**
 * 基本查询model
 * @author Chris
 *
 */
class AdminQuery extends CModule
{
	//缓存时间30秒
	private static $cachetime=30;
	//数据库单例 仿照CActiveRecord
	public static $db;
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
		if(self::$db!==null)
			return self::$db;
		else
		{
			self::$db=Yii::app()->getDb();
			if(self::$db instanceof CDbConnection)
				return self::$db;
			else
				throw new CDbException(Yii::t('yii','Active Record requires a "db" CDbConnection application component.'));
		}
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