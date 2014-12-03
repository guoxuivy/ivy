<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
use Ivy\db\Where;
use Ivy\db\pdo\mysql;
abstract class Model extends CComponent implements \IteratorAggregate, \ArrayAccess{
    
    //静态对象保存 节省性能开销
	private static $_models=array();
    protected $primaryKey =null;
    //用来存储表数据
    protected $attributes =null;
	
    
    public function __construct(){
        if($this->tableName()!==false){
            $this->getTableFields();
        }
    }
    
    public static function model()
	{
        $className=get_called_class();
		if(isset(self::$_models[$className])){
            return self::$_models[$className]; 
		}else{
			$model=self::$_models[$className]=new $className(null);
			return $model;
		}
	}
    
 
    /**
     *支持对象的数组用法 ArrayAccess IteratorAggregate方法实现 
     **/
    function offsetGet($offset){
        return $this->$offset;
    }
    function offsetSet($offset,$item){
        return $this->$offset=$item;
    }
    public function offsetExists($offset) {
        return property_exists($this,$offset);
    }
    public function offsetUnset($offset) {
        unset($this->$offset);
    }
    public function getIterator()
	{
		$attributes=$this->attributes;
		return new \ArrayIterator($attributes);
	}
    
    
    function __get($proName){
        if(isset($this->attributes[$proName])){
            return $this->attributes[$proName];
        }
        return parent::__get($proName);
    }
    function __set($proName,$value){
        if(in_array($proName,array_keys($this->attributes))){
            return $this->attributes[$proName]=$value;
        }
        return parent::__set($proName,$value);
        
    }
    

	public function getWhere(){
		return new Where();
	}
    
    public function getDb(){
		return \Ivy::app()->getDb();
	}
    
    /**
    * 获得model的类名
    */
    public function getModelClass()
    {
        $modelclass = get_class($this);
        return $modelclass;
    }
	
    /**
    * 更新、插入数据库
    */
    public function save()
	{
	   $pk_field=$this->primaryKey;
	   if($this->$pk_field===null){
	       $_pk = $this->db->InsertData($this->tableName(),$this->attributes);
           $this->$pk_field=$_pk;
           return $_pk;
	   }else{
	       $where = $this->where->eqTo($pk_field,$this->$pk_field);
	       return $this->db->updateDataByCondition($this->tableName(),$where,$this->attributes);
	   }
	}
    
    /**
     *仅仅支持int行主键 
     * @return object tableModel
     **/
    public function findByPk($pk)
    {
        $pk_field=$this->primaryKey;
        $pk=(int)$pk;
        if(empty($pk_field)||empty($pk)){
            throw new CException('主键异常！');
        }
        $where = $this->where->eqTo($pk_field,$pk);
        $res = $this->db->find($this->tableName(),$where);
        if($res){
            $m = new $this->modelClass;
            $m->attributes=$res;
            return $m;
        }else{
            return null;
        }
    }
    
    public function find($condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		$res = $this->db->find($this->tableName(),$condition,$colmnus,$order,$limit,$offset);
        if($res){
            $m = new $this->modelClass;
            $m->attributes=$res;
            return $m;
        }else{
            return null;
        }
        
	}
    
    public function findAll($condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		return $this->db->findAll($this->tableName(),$condition,$colmnus,$order,$limit,$offset);
	}
    
    
    /**
     *获取表信息 更类属性
     **/
    protected function getTableFields()
    {
        $tableName=$this->tableName();
        $fields = $this->db->findAllBySql("DESCRIBE `{$tableName}`");
        if(!$fields){
            throw new CException("模型-{$tableName}-初始化失败");
        }
        foreach($fields as $fie){
            $this->attributes[$fie['Field']]=$fie['Default'];
            if("PRI"===$fie['Key']) $this->primaryKey=$fie['Field'];
        }
    }
    

    /**
	 * @return string database table name
	 */
	abstract function tableName();
	

}