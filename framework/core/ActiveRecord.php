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
abstract class ActiveRecord extends Model implements \IteratorAggregate, \ArrayAccess ,\JsonSerializable{

	private $_fields = array();                 // meta data 存储数据库表对应字段名  数字索引数组
	private $_new=false;                        // whether this instance is new or not
	private $_attributes=array();               // attribute name => attribute value 字符串索引数组
	private $_pk=array();                       // 主键字段名数组
	protected $_alias='t';                      // the table alias being used for query

	protected $_cache = true;					//AR级别的自动缓存  针对 model的select （findBySql、findAllBySql）相关自动维护缓存

	private $_one_cache = array();				//针对连贯操作 单次是否使用缓存,具有最高优先级  array('open'=>true,'time'=>3600)

	/**
	 * 初始化AR
	 * 完成 _fields、_attributes、初始化
	 */
	public function __construct($config=null){
		if( is_null($config) && is_null($this->_config) ){
			$config = \Ivy::app()->C('db_pdo');
		}
		if($config)
			$this->_config = $config;

		if($this->_cache&&$config['ARcache']){
			$this->attachBehavior(new ActiveRecordCache($this),'ARcache');//缓存扩展功能注入
		}else{
			$this->_cache=false;
		}
		$this->initTableFields();
		$this->setIsNewRecord(true);
		parent::__construct();
	}

	/**
	 * 缓存开关的 连贯操作扩展 单次查询开关
	 * @param  [boonle] $open [description]
	 * @return [obj]        [description]
	 */
	public function cache($open=true,$time=null){
		$this->_one_cache['open'] = $open;
		if($open===true){
			$time = (int)$time;
			$ARcacheTime = $this->_config['ARcache']?$this->_config['ARcache']:3600;
			$this->_one_cache['time'] = $time?$time:$ARcacheTime;
		}
		return $this;
	}

	/**
	 * 重写 单条记录查询 兼容cache
	 * @param  [type] $sql [description]
	 * @return array	  二维数组
	 */
	public function findBySql($sql){
		if(!empty($this->_one_cache)){
			if($this->_one_cache['open']===true){
				$this->_one_cache = array();
				$this->attachBehavior(new ActiveRecordCache($this),'ARcache');//强制检查缓存扩展功能注入 
				return $this->getBehavior('ARcache')->findBySqlCache($sql,$this->_one_cache['time']);
			}
			if($this->_one_cache['open']===false){
				$this->_one_cache = array();
				return parent::findBySql($sql);
			}
			$this->_one_cache = array();
		}

		if($this->_cache)
			return $this->getBehavior('ARcache')->findBySqlCache($sql);
		return parent::findBySql($sql);
	}

	/**
	 * 重写 多条记录查询 兼容cache
	 * @param  [type] $sql [description]
	 * @return array   三维数组
	 */
	public function findAllBySql($sql){
		if(!empty($this->_one_cache)){
			if($this->_one_cache['open']===true){
				$this->_one_cache = array();
				$this->attachBehavior(new ActiveRecordCache($this),'ARcache');//强制检查缓存扩展功能注入 
				return $this->getBehavior('ARcache')->findAllBySqlCache($sql,$this->_one_cache['time']);
			}
			if($this->_one_cache['open']===false){
				$this->_one_cache = array();
				return parent::findAllBySqlCache($sql);
			}
			$this->_one_cache = array();
		}
		
		if($this->_cache)
			return $this->getBehavior('ARcache')->findAllBySqlCache($sql);
		return parent::findAllBySql($sql);
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

    public function jsonSerialize()
    {
        return $this->_attributes;
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
	 *获取表信息 更类属性 缓存1小时
	 **/
	protected function initTableFields(){
		$tableName=$this->tableName();
		if($this->_cache){
			//表结构缓存
			$DNS=md5($this->_config['dsn']);
			$key=md5($DNS."@talbe_fields@".$tableName);
			$fields = \Ivy::app()->cache->get($key);
			if($fields===false){
				$fields = $this->findAllBySql("DESCRIBE `{$tableName}`");
				\Ivy::app()->cache->set($key,$fields,3600);//缓存1小时
			}
		}else{
			$fields = $this->findAllBySql("DESCRIBE `{$tableName}`");
		}
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
		return $this;
	}

	//前置操作会影响save的执行
	public function beforeSave(){
		return true;
	}
	//后置操作不影响save的执行
	public function afterSave(){
		return true;
	}

	/**
	 * 更新、插入 自动验证
	 * 修改为报错失败直接抛出异常
	 * @param  boolean $runValidation [description]
	 * @return [type]                 [description]
	 */
	public function save($runValidation=true){
		if($this->getAttributes()==null){
			throw new CException('无属性，保存失败！');
		}
		if(!$runValidation || $this->validate()){
			try {
				if(!$this->beforeSave())
					throw new CException('前置保存失败！');
				$res = $this->getIsNewRecord() ? $this->_insert() : $this->_update();
				$res->afterSave();
				return $res;
			} catch (CException $e) {
				$this->_error[]=$e->getMessage();
				throw new CException('保存失败！');
			}
		}else{
			throw new CException('属性验证失败，保存失败！');
		}
	}
	/**
	 * 验证器
	 * @return boolen
	 */
	public function validate(){
		$rules = $this->rules();
		if($rules){
			foreach ($this->getAttributes() as $key => $value) {
				foreach ($rules as $rule) {
					$check_keys=explode(',', preg_replace("/\s/","",$rule[0]));//带空格过滤
					if(in_array($key,$check_keys)){
						$check = $this->regex($value,$rule[1]);
						if($check===false){
							$this->_error[]="验证失败，属性{$key}不符合规则{$rule[1]}";
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * 验证规则 (可自定义正则验证)
	 * @return boolen
	 *  demo:
	 * 	return array(
	 *		array('room_id, name', 'require'),
	 *	 	array('room_id', 'number'),
	 *  );
	 */
	public function rules(){
		return false;
	}
	
	/**
	* 插入 AR插入
	* 返回 lastInsertId();
	* 隐藏此方法 统一由save提供
	*/
	private function _insert(){
		if($this->getIsNewRecord()){
			$lastId = $this->insertData($this->tableName(),$this->getAttributes());
			if($lastId>0){
				//自曾主键
				$key=$this->_pk[0];
				$this->_attributes[$key]=$lastId;
				if($this->_cache)
					$this->getBehavior('ARcache')->flush();
			}
			$this->setIsNewRecord(false);//插入成功标记为非新记录
			$this->lastSql=$this->db->lastSql;
			$this->refresh();
			$obj= unserialize(serialize($this));
			return $obj;
		}else{
			throw new CException('这不是一个新数据，无法插入！');
		}
	}

	/**
	* 更新 AR对象
	* 隐藏此方法 统一由save提供
	*/
    private function _update(){
		if($this->getIsNewRecord()){
			throw new CException('这是一个新数据，无法更新！');
		}else{
			$this->updateData($this->tableName(),$this->getPk(),$this->getAttributes());
			if($this->_cache)
				$this->getBehavior('ARcache')->flush();
			$this->lastSql=$this->db->lastSql;
			$this->refresh();
			$obj= unserialize(serialize($this));
			return $obj;
		}
	}

    /**
     * 更新操作(无查询更新)
     * @param null $data
     * @return mixed
     * @throws CException
     */
    public function update($data=null){
        if(empty($data)){
            return $this->_update();
        }
        $res = $this->updateData($this->tableName(),$this->options['where'],$data);
        if($this->_cache)
            $this->getBehavior('ARcache')->flush();
        $this->lastSql=$this->db->lastSql;
        return $res;
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
	 *支持多维主键 
	 * @return object tableModel
	 **/
	public function findByPk($pk,$fresh=true){
		try {
			$map=$this->aliasPk($this->getPk($pk));
		} catch (CException $e) {
			return null;
		}
		return $this->find($map,$fresh);
	}
	/**
	 * 定点查询
	 * @param  [map] $condition [查询条件]
	 * @param  boolen  $fresh   [是否刷新this对象属性]
	 * @return [objModel]       [对象复制返回]
	 */
	public function find($condition=NULL,$fresh=true){
        $this->where($condition);
		$res = $this->findBySql($this->buildSelectSql());
		if($res){
			if($fresh){
				$this->setAttributes($res,false);
				$this->setIsNewRecord(false);
			}
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
     * 分批数据返回处理 
     * 仅支持单主键表 
     * @access public
     * @param integer  $count    每次处理的数据数量
     * @param callable $callback 处理回调方法
     * @param string   $column   分批处理的字段名
     * @return boolean
     */
    public function chunk($count, $callback, $column = null)
    {
        $self = unserialize(serialize($this)); //拷贝对象,以免内部查询污染
        $key  = $self->_pk[0];
        if(empty($key)) 
        	throw new CException('仅支持单主键表 ！');
        	
        $resultSet  = $self->limit($count)->order($key, 'asc')->findAllBySql($self->buildSelectSql(true,false));
        $this->lastSql = $self->getLastSql();
        while (!empty($resultSet)) {
            if (false === call_user_func($callback, $resultSet)) {
                return false;
            }
            $end       = end($resultSet);
            $lastId    = $end[$key];
            $resultSet = $self->where([$key=>['gt', $lastId]])->findAllBySql($self->buildSelectSql(true,false));
            $this->lastSql = $self->getLastSql();
        }
        return true;
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
	 * @return [type]            [影响行数]
	 */
	public function deleteByPk($pk=null) {
		$num = $this->deleteData($this->tableName(),$this->getPk($pk));
		if($this->_cache&&$num)
			$this->getBehavior('ARcache')->flush();
		return $num;
	}

	/**
	 * 删除指定记录
	 * @param  [type] $where [description] 条件同 where 的结构
	 * @return [type]            [影响行数]
	 */
	public function deleteAll($where=null) {
		if(empty($where)) return false;
		$num = $this->deleteData($this->tableName(),$where);
		if($this->_cache&&$num)
			$this->getBehavior('ARcache')->flush();
		return $num;
	}

	/**
	 * 刷新AR对象
	 * 无主键的表无需刷新
	 * @return [type] [description]
	 */
	public function refresh(){
		try {
			$this->where($this->getPk());
			$record = $this->findBySql($this->buildSelectSql(false));
			if($record!==null){
				$this->setAttributes($record,false);
				$this->setIsNewRecord(false);
				return true;
			}else{
				return false;
			}
		} catch (CException $e) {
			return true;
		}	
	}

	/**
	 * 将对象强制刷新为新对象 防止插入数据时模型被查询结果污染
	 * @return [type] [description]
	 */
	protected function _new(){
		$this->_new = true; 
		$this->_attributes = array();
		return $this;
	}

	/**
	 * 抽象方法 获取表名
	 * @return string database table name
	 */
	abstract function tableName();
}