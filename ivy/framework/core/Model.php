<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 *
 * 普通数据库操作对象 提供复杂的自定义数据库操作
 */
namespace Ivy\core;
use Ivy\db\Where;
class Model extends CComponent{
    
    //静态对象保存 节省性能开销
	private static $_models=array();
    //错误搜集
    protected      $_error = array();
    
    public function __construct($do=null){
        $this->init();
    }
    /**
     * 返回模型对象实例
     * @return obj 
     */
    public static function model()
	{
        $className = get_called_class();
		if(isset(self::$_models[$className])){
            return self::$_models[$className]; 
		}else{
			$model = self::$_models[$className] = new $className(null);
			return $model;
		}
	}
    /**
     * 初始化后调用
     */
    public function init(){
    }

    /**
     * 生产新where实例
     * @return [type] [description]
     */
	public function getWhere(){
		return new Where();
	}
    /**
     * 返回数据库对象句柄
     * @return [type] [description]
     */
    public function getDb(){
		return \Ivy::app()->getDb();
	}
    
    /**
    * 获得model的类名
    */
    public function getModelClass(){
        $modelclass = get_class($this);
        return $modelclass;
    }

    /**
     * 单条记录查询
     * @param  [type] $sql [description]
     * @return array      二维数组
     */
    public function find($sql){
        return $this->db->findBySql($sql);
        
    }

    /**
     * 多条记录查询
     * @param  [type] $sql [description]
     * @return array   三维数组
     */
    public function findAll($sql){
        return $this->db->findAllBySql($sql);
    }

    /**
     * 多条记录查询
     * @param  [type] $sql [description]
     * @return array   三维数组
     */
    public function exec($sql){
        return $this->db->exec($sql);
    }

}