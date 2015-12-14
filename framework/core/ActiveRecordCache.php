<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 *
 * 为ActiveRecord提供缓存扩展
 * 3层缓存结构 使AR能自动更新关联表缓存 保证多表查询准确性
 * DNS@talbe_groups
 * -------------------------------
 * |      | table1+table2       |
 *     ----------------
 *     |       |       |
 *     sql1    sql2    sql3
 */
namespace Ivy\core;
class ActiveRecordCache{

	protected $AR=NULL;
	protected $DNS=NULL;

	protected $_cacheTime = 600;				//缓存时间 单位秒 缓存开启时有效
	
	private static $all_tables = false;			//数据库的所有表名称
	
	/**
	 * 初始化AR
	 * 完成 _fields、_attributes、初始化
	 */
	public function __construct(&$AR){
		$this->AR=$AR;
		$this->DNS=\Ivy::getBaseUrl(true);
	}

	/**
	 * 添加查询结果缓存 缓存时间单位秒
	 * @return [array] [description]
	 */
	private function addSelectCache($sql,$value){
		$tables = $this->getTables($sql);
		$md5sql=md5($sql);
		//sql查询值缓存 第三级别
		\Ivy::app()->cache->set($md5sql,$value,$this->_cacheTime);
		
		//更新表组合中的sql索引缓存 第二级别
		$tables_key=$tables;
		array_unshift($tables_key,$this->DNS);//加入域名标识符
		$tables_key = implode('@', $tables_key);
		$tables_md5sqls = \Ivy::app()->cache->get($tables_key);//table组合下的sql缓存索引
		if(!$tables_md5sqls) $tables_md5sqls=array();
		if(!in_array($md5sql, $tables_md5sqls)){
			$tables_md5sqls[]=$md5sql;
			\Ivy::app()->cache->set($tables_key,$tables_md5sqls,$this->_cacheTime); 
		}

		//将该表组合映射到当前AR的表管理器缓存  第一级别
		$ar_key=md5($this->DNS."@talbe_groups");
		$ar_tables = \Ivy::app()->cache->get($ar_key);
		if(!$ar_tables) $ar_tables=array();
		if(!in_array($tables_key, $ar_tables)){
			$ar_tables[]=$tables_key;
			\Ivy::app()->cache->set($ar_key,$ar_tables,$this->_cacheTime); 
		}
	}

	/**
	 * 获取查询结果缓存
	 * @return [array] [description]
	 */
	private function getSelectCache($sql){
		$md5sql=md5($sql);
		$res = \Ivy::app()->cache->get($md5sql);
		return $res;
	}

	/**
	 * 删除AR关联的所有缓存 三级联动删除 AR更新时触发
	 * @return [array] [description]
	 */
	private function flushSelectCache(){
		//第一级 所有的表组合获取
		$group_key=md5($this->DNS."@talbe_groups");
		$group_tables = \Ivy::app()->cache->get($group_key);
		foreach($group_tables as $tables_key){
			$group = explode('@', $tables_key);
			array_shift($group);
			//如果影响此分组
			if(in_array($this->AR->tableName(),$group)){
				//获取该分组所有的sql 并清空
				$group_slq_md5 = \Ivy::app()->cache->get($tables_key);
				foreach ($group_slq_md5 as $md5sql) {
					\Ivy::app()->cache->delete($md5sql); //删除第三级别
				}
			}
		}
	}

	//无缓存查询
	public function find($sql){
		return $this->AR->getDb()->findBySql($sql);
	}
	//无缓存查询
	public function findAll($sql){
		return $this->AR->getDb()->findAllBySql($sql);
	}
	//释放缓存
	public function flush(){
		return $this->flushSelectCache();
	}

	//兼容cache的单记录查询
	public function findBySqlCache($sql){
		$res = $this->getSelectCache($sql);
		if($res===false){
			$res = $this->find($sql);
			$this->addSelectCache($sql,$res);
		}
		return $res;
	}
	//兼容cache的多记录查询
	public function findAllBySqlCache($sql){
		$res = $this->getSelectCache($sql);
		if($res===false){
			$res = $this->findAll($sql);
			$this->addSelectCache($sql,$res);
		}
		return $res;
	}

	/**
	 * 获取数据库所有表集合
	 * 异地表可能失效
	 * @return [array] 
	 */
	private function showTablse(){
		if(self::$all_tables === false){	
			$key  = md5($this->DNS."@all_tables");
			$tables = \Ivy::app()->cache->get($key); 
			if($tables===false){
				$list = $this->findAll("SHOW TABLES");
				$tables = array();
				foreach($list as $value){
					$tables[]=array_shift($value);
				}
				\Ivy::app()->cache->set($key,$tables,$this->_cacheTime); 
			}
			self::$all_tables=$tables;
		}
		return self::$all_tables;
	}

	/**
	 * 分析 查询SQL 获取所有关联的表名
	 * @return [array] [description]
	 */
	private function getTables($sql){
		$list1=$this->getFromTables($sql);
		$list2=$this->getJoinTables($sql);
		$res = array_merge($list1,$list2);
		sort(array_unique($res));
		return $res;
	}
	/**
	 * form 相关分析
	 * 以form 为分隔符 
	 * @return [array] [description] 自然排序
	 */
	private function getFromTables($sql){
		$res = array();
		$list = preg_split("/from/ism",$sql);
		foreach ($list as $key => $str) {
			if ($key==0) continue; //第一个为select内容 跳过
			$f_str = array_shift(array_filter(explode(' ', $str)));//第一个字符串 可能为表名称
			if(substr( $f_str, 0, 1 )=='(') continue;		//子查询直接跳过
			$f_str = str_replace(')', '', $f_str);	//右括号清理
			$f_str = str_replace('`', '', $f_str);	//sql转义符过滤
			if(!in_array($f_str, $this->showTablse())) continue;
			$res[]=$f_str;
		}
		return $res;
	}
	/**
	 * join 相关分析
	 * 以form 为分隔符 
	 * @return [array] [description] 自然排序
	 */
	private function getJoinTables($sql){
		$res = $list = array();
		preg_match_all("/join(.*?)on/ism",$sql,$list);//匹配 join 中的表名
		foreach ($list[1] as $str) {
			$f_str = array_shift(array_filter(explode(' ', $str)));//第一个字符串 可能为表名称
			if(substr( $f_str, 0, 1 )=='(') continue;		//子查询直接跳过
			$f_str = str_replace('`', '', $f_str);	//sql转义符过滤
			if(!in_array($f_str, $this->showTablse())) continue;
			$res[]=$f_str;
		}
		return $res;
	}



}