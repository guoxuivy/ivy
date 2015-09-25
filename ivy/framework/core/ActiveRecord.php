<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
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
	// private $_related=array();                  // attribute name => related objects
	private $_pk=array();                       // 主键字段名数组
    
	protected $_alias='t';                        // the table alias being used for query

    
	/**
	 * 初始化AR
	 * 完成 _fields、_attributes、初始化
	 */
	public function __construct($config=null){
		parent::__construct($config);
		$this->initTableFields();
		$this->setIsNewRecord(true);
	}

	/**
	 * 重写table方法
	 * @access public
	 * @param mixed $page 页数
	 * @param mixed $listRows 每页数量
	 * @return Model
	 */
	public function table($table=null){
	    $this->options['table']= array($this->tableName()=>$this->_alias);//默认table 为当前AR
	    return $this;
	}


	/**
	 *获取表信息 更类属性
	 **/
	protected function initTableFields(){
		$tableName=$this->tableName();
		$fields = $this->findAllBySql("DESCRIBE `{$tableName}`");
		if(!$fields){
			throw new CException("模型-{$tableName}-初始化失败");
		}
		foreach($fields as $fie){
			$this->_fields[$fie['Field']]=$fie['Type'];
			if("PRI"===$fie['Key']) $this->_pk[]=$fie['Field'];
		}
	}


	/**
	 * 判断数据库是否存在该记录 主键判断
	 * @return boolen
	 */
	private function checkNewRecord(){
		try{
			$pri=$this->aliasPk($this->getPk());

			$where = $this->db->parseWhere($pri);

			$sql="SELECT * FROM `".$this->tableName()."` `".$this->_alias."` ".$where;
	
			$record = $this->findBySql($sql);
		
			if($record!=null){
				$this->setIsNewRecord(false);
			}else{
				$this->setIsNewRecord(true);
			}
		}catch(CException $e){
			$this->setIsNewRecord(true);
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
			$this->_attributes[$proName]=$value;
			//如果传入主键 则检测刷新new
			if(in_array($proName, $this->_pk)&&$value!==null){
				$this->checkNewRecord();
			}
			return true;
		}
		return parent::__set($proName,$value);
	}
	//_attributes中数据 判断
	public function __isset($proName)
	{
		if(array_key_exists($proName , $this->_attributes)){
			return true;
		}
		return parent::__isset($proName);
	}

	/**
	 * 返回标准化的主键值数组
	 * @param   $pri 传入值 如果默认则返回当前对象主键数组 如果是数组 应该是array('id'=>123)的形式
	 * @return array 返回关键字数组 array('id'=>123)
	 */
	public function getPk($pri=null){
		if(empty($this->_pk)) throw new CException('表主键未设置！');
		$pk = array();
		if($pri===null){
			foreach($this->_pk as $key){
				if($this->$key==null){
					throw new CException('主键不全！'.$this->$key); 
				}
				$pk[$key]=$this->$key;
			}
		}elseif(is_array($pri)){
			foreach($this->_pk as $key){
				if($pri[$key]==null) throw new CException('主键不全！');
				$pk[$key]=$pri[$key];
			}
		}elseif(is_int($pri)||is_string($pri)){
			$pk[$this->_pk[0]]=$pri;
		}else{
			throw new CException('主键参数错误！');
		}
		return $pk;
	}

	/**
	 * 返回 _attributes赋值  fields安全
	 */
	public function getAttributes($type='save'){
        $arr=array();
        if($type=='all')
            return $this->_attributes;
        foreach($this->_attributes as $key => $value){
            if(array_key_exists($key, $this->_fields)){
				$arr[$key]=$value;
			}
        }
		return $arr;
	}

	/**
	 * _attributes赋值 fields安全
	 * @param [type] $attributes [description]
	 */
	public function setAttributes($attributes,$check=true){
		foreach ($attributes as $key => $value) {
			$this->_attributes[$key]=$value;
            if($check && array_key_exists($key, $this->_fields)){
				//如果传入主键 则检测刷新new
				if(in_array($key, $this->_pk)&&$value!==NULL){
					$this->checkNewRecord();
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

	public function beforeSave(){
		return true;
	}
	public function afterSave(){
		return true;
	}

	/**
	 * 更新、插入 自动验证
	 * @param  boolean $runValidation [description]
	 * @return [type]                 [description]
	 */
	public function save($runValidation=true){
		if($this->getAttributes()==null){
			return false;
		}
		if(!$runValidation || $this->validate()){
			try {
				if(!$this->beforeSave())
					return false;
				$res = $this->getIsNewRecord() ? $this->insert() : $this->update();
				$this->lastSql=$this->db->lastSql;
				if(!$this->afterSave())
					return false;
				return $res;
			} catch (CException $e) {
				$this->_error[]=$e->getMessage();
				return false;
			}
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
	* 插入 AR插入
	* 返回 lastInsertId();
	*/
	public function insert(){
		if($this->getIsNewRecord()){
			$lastId = $this->insertData($this->tableName(),$this->getAttributes());
			if($lastId>0){
				//自曾主键
				$key=$this->_pk[0];
				$this->_attributes[$key]=$lastId;
			}else{
				$this->setIsNewRecord(false);
			}
			$obj= unserialize(serialize($this));
			return $obj;
		}else{
			throw new CException('这不是一个新数据，无法插入！');
		}
	}

	/**
	* 更新 AR对象
	*/
	public function update(){
		if($this->getIsNewRecord()){
			throw new CException('这是一个新数据，无法更新！');
		}else{
			$this->updateData($this->tableName(),$this->getPk(),$this->getAttributes());
			$obj= unserialize(serialize($this));
			return $obj;
		}
	}

	
	/**
	 * 给主键添加别名前缀 byPk时可以兼容
	 * @return [type] [description]
	 */
	protected function aliasPk($pk=null){
		if(empty($pk)) throw new CException('无主键！');
		$arr=array();
		foreach($pk as $p=>$v){
			if(substr($p,0,2)!=$this->_alias.'.')
				$arr[$this->_alias.'.'.$p]=$v;
			else
				$arr[$p]=$v;
		}
		return $arr;
	}


	/**
	 *仅仅支持int行主键 
	 * @return object tableModel
	 **/
	public function findByPk($pk){
		$map=$this->aliasPk($this->getPk($pk));
		return $this->find($map);
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
	public function find($condition = NULL){
        $this->where($condition);
		$res = $this->findBySql($this->buildSelectSql());
		if($res){
			$this->setAttributes($res,false);
			$this->setIsNewRecord(false);
			$obj= unserialize(serialize($this));
			return $obj;
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
	public function findAll($condition = NULL) {
		$this->where($condition);
		return $this->findAllBySql($this->buildSelectSql());
	}
	

	/**
	 * 删除本记录
	 * @param  [type] $condition [description]
	 * @return [type]            [description]
	 */
	public function delete() {
		if(!$this->getIsNewRecord()){
			$pk=$this->getPk();
			return $this->deleteByPk($pk);
		}else{
			throw new CException('新记录无法删除！');
		}
	}

	/**
	 * 删除指定记录
	 * @param  [type] $condition [description]
	 * @return [type]            [description]
	 */
	public function deleteByPk($pk=null) {
		return $this->deleteData($this->tableName(),$this->getPk($pk));
	}

	/**
	 * 删除指定记录
	 * @param  [type] $where [description] 条件同 where 的结构
	 * @return [type]            [description]
	 */
	public function deleteAll($where=null) {
		if(empty($where)) return false;
		return $this->deleteData($this->tableName(),$where);
	}

	/**
	 * 刷新AR对象
	 * @return [type] [description]
	 */
	public function refresh(){
		$where=$this->getPk();
		if(($record=$this->find($where))!==null){
			$this->setAttributes($record,false);
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