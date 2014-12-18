<?php
 /**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * PDO数据库操作类
 */
namespace Ivy\db\pdo;
use Ivy\core;
use Ivy\db\AbsoluteDB;
class mysql extends AbsoluteDB {

	//事物标记
	private $_begin_transaction = false;

	public function __construct($config) {
		try {
			$this->pdo = new \PDO ( $config ['dsn'], $config ['user'], $config ['password'] );
			$this->pdo->exec('set names utf8');
		} catch ( PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
	}

	/**
	 * 事务开始
	 * @return [boolen] [description]
	 */
	public function beginT(){
		//如果已经有事物在运行 则先回滚
		if($this->_begin_transaction){
			throw new CException ( '还有有事务未提交！' );
		}
		$this->_begin_transaction = false;
		$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
		$res = $this->pdo->beginTransaction();
		if($res) $this->_begin_transaction = true;
		return $res;
	}

	/**
	 * 事物回滚
	 * @return boolen 
	 */
	public function rollbackT(){
		$this->_begin_transaction = false;
		$res = $this->pdo->rollback();
		//恢复自动提交
		$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
		return $res;

	}

	/**
	 * 事务提交
	 * @return [boolen] 
	 */
	public function commitT(){
		if($this->_begin_transaction){
			$this->_begin_transaction = false;
			$res = $this->pdo->commit();
			//恢复自动提交
			$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
			return $res;
		}
		return false;
	}

	
	/**
	 * 执行删改语句，返回受影响行数
	 * @param string $sql;
	 * @return int affectRow;
	 */
	public function getAffectRowNum($sql) {
		return $this->pdo->exec( $sql );
	}
	
	/**
	 * 插入数据，返回最后插入行的ID或序列值
	 * @param string $tableName 表名
	 * @param array $data
	 * @return int id;
	 */
	public function InsertData($tableName, $data) {
		$sql = $this->getInsertSql($tableName, $data);
		$this->exec( $sql );
		return $this->pdo->lastInsertId();
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
	public function findAll($tableName, $condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		$sql = $this->getSelectSql($tableName, $condition, $colmnus,$order,$limit,$offset);
        $res = $this->pdo->query( $sql );
		if(!$res) return false;
		return $res->fetchAll(\PDO::FETCH_ASSOC);
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
	public function find($tableName, $condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		$sql = $this->getSelectSql($tableName, $condition, $colmnus,$order,$limit,$offset);
		$res = $this->pdo->query( $sql );
		if(!$res) return false;
		return $res->fetch(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * 执行sql返回结果集
	 * @param string $sql;
	 * @return array;
	 */
	public function findAllBySql($sql){
		$res = $this->pdo->query($sql);
        if(!$res) return false;
		return $res->fetchAll(\PDO::FETCH_ASSOC);
	}
    
    /**
	 * 执行sql返回结果集
	 * @param string $sql;
	 * @return array;
	 */
	public function findBySql($sql){
		$res = $this->pdo->query($sql);
        if(!$res) return false;
		return $res->fetch(\PDO::FETCH_ASSOC);
	}
    
    /**
	 * 执行sql
	 * @param string $sql;
	 */
	public function exec($sql){
		try {
			$res = $this->pdo->exec( $sql );
		} catch ( PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
		return $res;
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
		$count = $this->findBySql($sql);
		$data['pageNums'] = (int)ceil($count['count']/$limit);
		$data['currentpage'] = $page>0 ? $page : 1;
		$data['currentpage'] = $data['currentpage'] > $data['pageNums'] ? $data['pageNums'] : $data['currentpage'];
		$offset = ($data['currentpage']-1)*$limit;
		$data['data'] = $this->findAll($tableName, $condition, $colmnus,$order,$limit,$offset);
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
		return $this->exec( $sql );
	}
	
	/**
	 * 根据条件删除数据
	 */
	public function deleteDataByCondition($tableName,$Condition){
		$sql = $this->getDeltetSql($tableName,$Condition);
		return $this->exec( $sql );
	}
}