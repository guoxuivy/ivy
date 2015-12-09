<?php
 /**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * PDO数据库操作类
 * 所有PDO异常转换为CException 异常
 */
namespace Ivy\db\pdo;
use Ivy\db\AbsoluteDB;
use Ivy\db\DBException;
use Ivy\logging\CLogger;
class mysql extends AbsoluteDB {
	const SQL_ERROR='sql_error';
	//连接句柄池
	private $pdo = null;
	//事物标记 多层嵌套 只有最外层有效 参考laravel方案
	private $_transaction_level = 0;

	public $lastSql="";
	//sql性能分析
	public $enableProfiling=false;


	public function __construct($config=null) {
		if(empty($config))
			throw new DBException( 'no DB config' );
		$this->config=$config;
		throw new DBException( 'no DB config' );
		$this->pdo = $this->connect($config);
		if(IVY_DEBUG && $config['profile'])
			$this->enableProfiling = true;
	}

	/**
	 * 连接数据库
	 * @param  [type] $config [description]
	 * @return [type]         [description]
	 */
	public function connect($config=null) {
		$config=is_null($config)?$this->config:$config;
		try {
			$params = array (
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'' ,
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				//\PDO::ATTR_PERSISTENT => true //持久连接配置
			);
			$pdo = new \PDO ( $config ['dsn'], $config ['user'], $config ['password'], $params);
			//$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			//$pdo->exec('set names utf8');
			return $pdo;
		} catch ( \PDOException $e ) {
			throw new DBException ( $e->getMessage(),$e->getCode(),$e);
		}
	}

	/**
	 * 连接句柄
	 * @return pdo
	 */
	protected function pdo(){
		return $this->pdo;
	}

	/**
	 * 事务开始
	 * @return [boolen] [description]
	 */
	public function beginT(){
		++$this->_transaction_level;
		if ($this->_transaction_level == 1){
			//关闭自动提交
			$this->pdo()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
			$this->pdo()->beginTransaction();
		}
	}

	/**
	 * 事物回滚
	 * @return boolen 
	 */
	public function rollbackT(){
		if ($this->_transaction_level == 1){
			$this->_transaction_level = 0;
			$this->pdo()->rollback();
			//恢复自动提交
			$this->pdo()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
		}else{
			--$this->_transaction_level;
		}
	}

	/**
	 * 事务提交
	 * @return [boolen] 
	 */
	public function commitT(){
		if ($this->_transaction_level == 1){
			$res = $this->pdo()->commit();
			//恢复自动提交
			$this->pdo()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
		}
		--$this->_transaction_level;
	}



	/**
	 * 插入数据，返回最后插入行的ID或序列值
	 * @param string $tableName 表名
	 * @param array $data
	 * @return int id;
	 */
	public function insertData($tableName, $data) {
		$sql = $this->getInsertSql($tableName, $data);
		$this->_exec( $sql );
		return $this->pdo()->lastInsertId();
	}


	/**
	 * 执行sql返回结果集
	 * @param string $sql;
	 * @return array;
	 */
	public function findAllBySql($sql){
		$res = $this->_query($sql);
		if(!$res) return null;
		return $res->fetchAll(\PDO::FETCH_ASSOC);
		
	}

	/**
	 * 执行sql返回单条结果
	 * @param string $sql;
	 * @return array;
	 */
	public function findBySql($sql){
		$res = $this->_query( $sql );
		if(!$res) return null;
		return $res->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * 执行查询类sql
	 * @param 		string $sql
	 * @return  	pdo->res
	 * @throws   	CException
	 */
	public function _query($sql){
		try {
			$this->lastSql = $sql;
			if($this->enableProfiling){
				\Ivy::log('begin:'.$sql, CLogger::LEVEL_PROFILE, "query");
				$res = $this->pdo()->query( $sql );
				\Ivy::log('end:'.$sql, CLogger::LEVEL_PROFILE, "query");
			}else{
				$res = $this->pdo()->query( $sql );
			}
			return $res;
		} catch ( \PDOException $e ) {
			\Ivy::log($sql,CLogger::LEVEL_ERROR,self::SQL_ERROR);
			throw new DBException ( $e->getMessage(),$e->getCode(),$e);
		}
	}
	/**
	 * 执行非查询类sql
	 * @param 		string $sql
	 * @return  	pdo->res
	 * @throws  	CException
	 */
	public function _exec($sql){
		try {
			$this->lastSql = $sql;
			if($this->enableProfiling){
				\Ivy::log('begin:'.$sql, CLogger::LEVEL_PROFILE, "exec");
				$res = $this->pdo()->exec( $sql );
				\Ivy::log('end:'.$sql, CLogger::LEVEL_PROFILE, "exec");
			}else{
				$res = $this->pdo()->exec( $sql );
			}
			return $res;
		} catch ( \PDOException $e ) {
			\Ivy::log($sql,CLogger::LEVEL_ERROR,self::SQL_ERROR);
			throw new DBException ( $e->getMessage(),$e->getCode(),$e);
		}
		
	}

	/**
	 * 根据条件更新数据
	 * @param string $tableName
	 * @param object $Condition
	 * @param array $data
	 */
	public function updateDataByCondition($tableName,$Condition,$data){
		$sql = $this->getUpdataSql($tableName,$Condition,$data);
		return $this->_exec( $sql );
	}

	/**
	 * 根据条件删除数据
	 */
	public function deleteDataByCondition($tableName,$Condition){
		$sql = $this->getDeltetSql($tableName,$Condition);
		return $this->_exec( $sql );
	}


	/**
	 * 字段和表名处理添加`
	 * @access protected
	 * @param string $key
	 * @return string
	 */
	protected function parseKey(&$key) {
		$key   =  trim($key);
		if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
			$key = '`'.$key.'`';
		}
		return $key;
	}

	/**
	 * 释放
	 */
	public function close() {
		$this->pdo=null;
	}
}