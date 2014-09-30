<?php
 /**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 * @comment PDO数据库操作类
 */
namespace Ivy\db\pdo;

use Ivy\core;
use Ivy\db\AbsoluteDB;
class mysql extends AbsoluteDB {
	public function __construct($config) {
		try {
			$this->db = new \PDO ( $config ['dsn'], $config ['user'], $config ['password'] );
			$this->db->exec('set names utf8');
		} catch ( CException $e ) {
			throw new CException ( $e->getMessage () );
		}
	}
	
	/**
	 * 执行删改语句，返回受影响行数
	 * @param string $sql;
	 * @return int affectRow;
	 */
	public function getAffectRowNum($sql) {
		return $this->db->exec ( $sql );
	}
	
	/**
	 * 插入数据，返回最后插入行的ID或序列值
	 * @param string $tableName 表名
	 * @param array $data
	 * @return int id;
	 */
	public function InsertData($tableName, $data) {
		$sql = $this->getInsertSql($tableName, $data);
		$this->db->exec ( $sql );
		return $this->db->lastInsertId ();
	}
	
	/**
	 * 查询并返回结果集
	 * @param string $tableName
	 * @param Object $condition
	 * @param array $colmnus
	 * @param array $order
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getData($tableName, $condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		
		$sql = $this->getSelectSql($tableName, $condition, $colmnus,$order,$limit,$offset);
		$res = $this->db->query ( $sql );
		
		return $res->fetchAll (\PDO::FETCH_ASSOC);
	}
    /**
	 * 查询并返回结果集
	 * @param string $tableName
	 * @param Object $condition
	 * @param array $colmnus
	 * @param array $order
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getDataOne($tableName, $condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		
		$sql = $this->getSelectSql($tableName, $condition, $colmnus,$order,$limit,$offset);
		$res = $this->db->query ( $sql );
		
		return $res->fetch (\PDO::FETCH_ASSOC);
	}
	
	/**
	 * 执行sql返回结果集
	 * @param string $sql;
	 * @return array;
	 */
	public function getDataBySql($sql){
		$res = $this->db->query($sql);
		return $res->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * 获取翻页信息
	 * @param string $tableName	表名
	 * @param array $order 排序
	 * @param int $limit 查询条数
	 * @param int $page 页码
	 * @return array
	 */
	public function getPagener($tableName,$order = array(),$limit,$page,$condition = NULL,$colmnus = array('*')){
		$data = array();
		if(($condition instanceof where) && $condition->getCond() != NULL){
			$sql = 'select count(1) as `count` from `'.$tableName .'` where '.$condition->getCond();
		}else{
			$sql = 'select count(1) as `count` from `'.$tableName.'`';
		}
		$count = $this->getDataBySql($sql);
		$data['pageNums'] = (int)ceil($count[0]['count']/$limit);
		$data['currentpage'] = $page>0 ? $page : 1;
		$data['currentpage'] = $data['currentpage'] > $data['pageNums'] ? $data['pageNums'] : $data['currentpage'];
		$offset = ($data['currentpage']-1)*$limit;
		$data['data'] = $this->getData($tableName, $condition, $colmnus,$order,$limit,$offset);
		return $this->generatePagener($data);
	}
	
	/**
	 * 根据条件更新数据
	 * @param string $tableName
	 * @param object $Condition
	 * @param array $data
	 */
	public function updateDataByCondition($tableName,$Condition,$data){
		$sql = $this->getUpdataSql($tableName,$Condition,$data);
		return $this->db->exec ( $sql );
	}
	
	/**
	 * 根据条件删除数据
	 */
	public function deleteDataByCondition($tableName,$Condition){
		$sql = $this->getDeltetSql($tableName,$Condition);
		return $this->db->exec ( $sql );
	}
}