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
abstract class Model implements \IteratorAggregate, \ArrayAccess{
	//缓存时间30秒
	private static $cachetime=30;
    //静态对象保存 节省性能开销
	private static $_models=array();
    //protected $fields =null;
    protected $primaryKey =null;
    protected $attributes =null;
	
    
    public function __construct(){
        $this->getTableFields();
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
 
    /**
     *支持对象的数组用法 ArrayAccess方法实现 
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
        $method="get".ucfirst($proName);
        if(method_exists($this,$method)){
            return $this->$method();
        }elseif(property_exists($this,$proName)){
            return $this->$proName;
        }elseif(isset($this->attributes[$proName])){
            return $this->attributes[$proName];
        }else{
            return false;
        }
    }
    function __set($proName,$value){
        $method="set".ucfirst($proName);
        if(method_exists($this,$method)){
            return $this->$method($value);
        }elseif(property_exists($this,$proName)){
            return $this->$proName=$value;
        }elseif(in_array($proName,array_keys($this->attributes))){
            return $this->attributes[$proName]=$value;
        }else{
            return false;
        }
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
     **/
    public function findByPk($pk)
	{
	   $pk_field=$this->primaryKey;
       $pk=(int)$pk;
	   //$sql="select * from `{$tableName}` where {$this->primaryKey} = {$pk} limit 1 ";
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
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'company' => array(self::BELONGS_TO, 'EmployCompany', 'stores'),
			'customer' => array(self::BELONGS_TO, 'CuCustomerInfo', 'custom_id'),		
		);
	}
    

    /**
	 * @return string the associated database table name
	 */
	abstract function tableName();
	

}