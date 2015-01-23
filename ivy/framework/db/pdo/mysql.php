<?php
 /**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * PDO数据库操作类
 * 所有PDO异常转换为CException 异常
 */
namespace Ivy\db\pdo;
use Ivy\core\CException;
use Ivy\db\AbsoluteDB;
use Ivy\db\Where;
class mysql extends AbsoluteDB {

	//事物标记
	private $_begin_transaction = false;

	public function __construct($config) {
		try {
			$this->pdo = new \PDO ( $config ['dsn'], $config ['user'], $config ['password'] );
			$this->pdo->exec('set names utf8');
		} catch ( \PDOException $e ) {
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
		try {
			$this->_begin_transaction = false;
			$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
			$res = $this->pdo->beginTransaction();
			if($res) $this->_begin_transaction = true;
			return $res;
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
	}

	/**
	 * 事物回滚
	 * @return boolen 
	 */
	public function rollbackT(){
		try {
			$this->_begin_transaction = false;
			$res = $this->pdo->rollback();
			//恢复自动提交
			$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
			return $res;
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
	}

	/**
	 * 事务提交
	 * @return [boolen] 
	 */
	public function commitT(){
		if($this->_begin_transaction){
			try {
				$this->_begin_transaction = false;
				$res = $this->pdo->commit();
				//恢复自动提交
				$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
				return $res;
			} catch ( \PDOException $e ) {
				throw new CException ( $e->getMessage () );
			}
		}
		return false;
	}

	
	
	/**
	 * 插入数据，返回最后插入行的ID或序列值
	 * @param string $tableName 表名
	 * @param array $data
	 * @return int id;
	 */
	public function InsertData($tableName, $data) {
		try {
			$sql = $this->getInsertSql($tableName, $data);
			$this->exec( $sql );
			return $this->pdo->lastInsertId();
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
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
		try {
			$sql = $this->getSelectSql($tableName, $condition, $colmnus,$order,$limit,$offset);
	        $res = $this->pdo->query( $sql );
			if(!$res) return false;
			return $res->fetchAll(\PDO::FETCH_ASSOC);
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
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
		try {
			$sql = $this->getSelectSql($tableName, $condition, $colmnus,$order,$limit,$offset);
			$res = $this->pdo->query( $sql );
			if(!$res) return false;
			return $res->fetch(\PDO::FETCH_ASSOC);
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
		
	}
	
	/**
	 * 执行sql返回结果集
	 * @param string $sql;
	 * @return array;
	 */
	public function findAllBySql($sql){
		try {
			$res = $this->pdo->query($sql);
	        if(!$res) return false;
			return $res->fetchAll(\PDO::FETCH_ASSOC);
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
		
	}
    
    /**
	 * 执行sql返回单条结果
	 * @param string $sql;
	 * @return array;
	 */
	public function findBySql($sql){
		try {
			$res = $this->pdo->query($sql);
	        if(!$res) return false;
			return $res->fetch(\PDO::FETCH_ASSOC);
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
	}
    
    /**
	 * 执行sql
	 * @param string $sql
	 * @return  返回影响行数 可能为 0
	 */
	public function exec($sql){
		try {
			$res = $this->pdo->exec( $sql );
		} catch ( \PDOException $e ) {
			throw new CException ( $e->getMessage () );
		}
		return $res;
	}
    
	
	/**
	 * 获取翻页信息
	 * @param string $tableName	表名
	 * @param array $order 排序
	 * @param int $limit 每页显示条数
	 * @param int $page 页码
	 * @return array
	 */
	public function getPagener($tableName,$condition = NULL,$page=1,$limit = 10,$colmnus = array('*'),$order = array()){
		$data = array();
		if(($condition instanceof Where) && $condition->getCond() != NULL){
			$sql = 'select count(1) as `count` from `'.$tableName .'` where '.$condition->getCond();
		}elseif(is_string($condition)){
			$sql = 'select count(1) as `count` from `'.$tableName .'` where '.$condition;
		}else{
			$sql = 'select count(1) as `count` from `'.$tableName.'`';
		}
		$count = $this->findBySql($sql);
		$data['recordsTotal'] = (int)$count['count'];
		$data['pageSize'] = (int)$limit;
		$data['pageNums'] = (int)ceil($count['count']/$limit);
		$data['currentPage'] = $page>0 ? $page : 1;
		$data['currentPage'] = $data['currentPage'] > $data['pageNums'] ? $data['pageNums'] : $data['currentPage'];
		$offset = ($data['currentPage']-1)*$limit;
		$data['list'] = $this->findAll($tableName, $condition, $colmnus,$order,$limit,$offset);
        return $data;
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