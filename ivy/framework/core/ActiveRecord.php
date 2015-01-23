<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 *
 * ORM 数据库记录对象映射 提供便捷的数据库对象映射
 */
namespace Ivy\core;
abstract class ActiveRecord extends Model implements \IteratorAggregate, \ArrayAccess{

    private $_fields = array();                 // meta data 存储数据库表对应字段名  数字索引数组
    private $_new=false;                        // whether this instance is new or not
    private $_attributes=array();               // attribute name => attribute value 字符串索引数组
    private $_related=array();                  // attribute name => related objects
    private $_c;                                // query criteria (used by finder only)
    private $_pk=null;                          // old primary key value
    private $_pk_col=null;                      // 主键对应的字段名
    private $_alias='t';                        // the table alias being used for query


    /**
     * 初始化AR
     * 完成 _fields、_attributes、_pk_col初始化
     */
    public function __construct($do=null){
        $this->initTableFields();
        $this->setIsNewRecord(true);
        $this->init();
    }


    /**
     *获取表信息 更类属性
     **/
    protected function initTableFields(){
        $tableName=$this->tableName();
        $fields = $this->db->findAllBySql("DESCRIBE `{$tableName}`");
        if(!$fields){
            throw new CException("模型-{$tableName}-初始化失败");
        }
        foreach($fields as $fie){
            $this->_fields[$fie['Field']]=$fie['Default'];
            if("PRI"===$fie['Key']) $this->_pk_col=$fie['Field'];
        }
    }

    public function getIsNewRecord(){
        return $this->_new;
    }
    public function setIsNewRecord($value){
        $this->_new=$value;
    }
    
    //优先读取_attributes中数据
    function __get($proName){
        if(array_key_exists($proName , $this->_attributes)){
            return $this->_attributes[$proName];
        }
        return parent::__get($proName);
    }
    //优先写入到_attributes数据中
    function __set($proName,$value){
        //赋值表内属性
        if(array_key_exists($proName, $this->_fields)){
                //如果传入主键 则不认为是新记录
                if($proName==$this->_pk_col&&$value!=null){
                    $this->setPk($value);
                    $this->setIsNewRecord(false);
                }

            return $this->_attributes[$proName]=$value;
        }
        return parent::__set($proName,$value);
    }

    /**
     * 主键值
     */
    public function getPk(){
        return $this->_pk;
    }
    public function setPk($v){
        return $this->_pk=$v;
    }

    /**
     * 返回 _attributes赋值
     */
    public function getAttributes(){
        return $this->_attributes;
    }

    /**
     * _attributes赋值 fields安全
     * @param [type] $attributes [description]
     */
    public function setAttributes($attributes){
        foreach ($attributes as $key => $value) {
            if(array_key_exists($key, $this->_fields)){
                $this->_attributes[$key]=$value;

                //如果传入主键 则不认为是新记录
                if($key==$this->_pk_col&&$value!=null){
                    $this->setPk($value);
                    $this->setIsNewRecord(false);
                }
            }
        }
    }

    /**
     *支持对象的数组用法 ArrayAccess IteratorAggregate方法实现 
     *仅对attribute 有效
     **/
    function offsetGet($offset){
        return $this->_attributes[$offset];
    }
    function offsetSet($offset,$item){
        return $this->_attributes[$offset]=$item;
    }
    public function offsetExists($offset){
        return property_exists($this->_attributes,$offset);
    }
    public function offsetUnset($offset){
        unset($this->_attributes[$offset]);
    }
    public function getIterator(){
        return new \ArrayIterator($this->_attributes);
    }


    public function save($runValidation=true){
        if($this->getAttributes()==null){
            return false;
        }
        if(!$runValidation || $this->validate()){
            return $this->getIsNewRecord() ? $this->insert() : $this->update();
        }else{
            return false;
        }
    }
    /**
     * 验证器
     * @return boolen
     */
    public function validate(){
        return true;
    }
	
    /**
    * 插入
    */
    public function insert(){
	   if($this->getIsNewRecord()){
	       $pk = $this->db->InsertData($this->tableName(),$this->getAttributes());
           $this->setPk($pk);
           $this->setIsNewRecord(false);
           return $pk;
	   }else{
	       throw new CException('这不是一个新数据，无法插入！');
	   }
	}
    /**
    * 更新
    */
    public function update(){
       if($this->getIsNewRecord()){
           throw new CException('这是一个新数据，无法更新！');
       }else{
           $where = $this->where->eqTo($this->_pk_col,$this->getPk());
           $this->db->updateDataByCondition($this->tableName(),$where,$this->getAttributes());
           return $this->getPk();
       }
       
    }
    
    
    /**
     *仅仅支持int行主键 
     * @return object tableModel
     **/
    public function findByPk($pk){
        $pk=(int)$pk;
        if(empty($this->_pk_col)||empty($pk)){
            throw new CException('主键异常！');
        }
        $where = $this->where->eqTo($this->_pk_col,$pk);
        return $this->find($where);
    }
    /**
     * 定点查询
     * @param  [type] $condition [description]
     * @param  array  $colmnus   [description]
     * @param  array  $order     [description]
     * @param  [type] $limit     [description]
     * @param  [type] $offset    [description]
     * @return [objModel]        [对象返回]
     */
    public function find($condition = NULL,$order = array(),$limit = NULL,$offset=NULL){
        $res = $this->db->find($this->tableName(),$condition,array('*'),$order,$limit,$offset);
        if($res){
            $this->setAttributes($res);
            $this->setIsNewRecord(false);
            $this->setPk($res[$this->_pk_col]);
            return $this;
        }else{
            return null;
        }
        
	}

    /**
     * 查询多条记录
     * @param  [type] $condition [description]
     * @param  array  $colmnus   [description]
     * @param  array  $order     [description]
     * @param  [type] $limit     [description]
     * @param  [type] $offset    [description]
     * @return [array]            [数组返回]
     */
    public function findAll($condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		return $this->db->findAll($this->tableName(),$condition,$colmnus,$order,$limit,$offset);
	}
    
    //默认每页10条记录
    public function getPagener($condition = NULL, $page=1,$limit = 10,$colmnus = array('*'),$order = array()) {
        return $this->db->getPagener($this->tableName(),$condition,$page,$limit,$colmnus,$order);
    }

    /**
     * 删除本记录
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function delete() {
        if(!$this->getIsNewRecord()){
            $pk=$this->getPk();
            if(empty($pk)){
                throw new CException('主键异常！');
            }
            $condition = $this->where->eqTo($this->_pk_col,$pk);
            $res = $this->db->deleteDataByCondition($this->tableName(),$condition);
            return $res;
        }else{
            throw new CException('新记录无法删除！');
        }
    }

    //更新AR对象
    public function refresh(){
        if(($record=$this->findByPk($this->getPk()))!==null){
            $this->setAttributes($record);
            $this->setIsNewRecord(false);
            return true;
        }else{
            return false;
        }
    }


    
    

    /**
     * 抽象方法 获取表名
	 * @return string database table name
	 */
	abstract function tableName();
	

}