<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\db;
use Ivy\core\CException;
abstract class AbsoluteDB {
	protected $pdo = NULL;
	/**
	 * 生成查询sql语句
	 */
	protected function getSelectSql($tableName, $condition = NULL, $colmnus = array('*'),$order = array() ,$limit = NULL,$offset=NULL) {
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		$sql = "select ";
		foreach ( $colmnus as $k => $v ) {
			if ($k > 0) {
				$sql .= ',`' . $v . '`';
			} else if ($v != '*') {
				$sql .= '`' . $v . '`';
			} else {
				$sql .= $v;
			}
		}
		$sql .= ' from `' . $tableName . '` ';

		if($condition == NULL){
			$sql .= ' where 1=1 ';
		}elseif($condition&&is_string($condition)){
			$sql .= ' where ' . $condition;
		}elseif($condition instanceof Where){
			if(trim($condition->getCond()) != ''){
				$sql .= ' where ' . $condition->getCond();
			}
		}else{
			throw new CException('无效的条件');
		}

		if(!empty($order)){
			$sql .= ' order by ';
			foreach ($order as $k => $v){
				$sql .= " `{$k}` {$v} ,";
			}
			$sql=substr($sql,0,-1);
		}

		if(isset($limit) && !isset($offset)){
			$sql .= ' limit ' . $limit;
		}else if(isset($limit) && isset($offset)){
			$sql .= ' limit ' . $offset.','.$limit;
		}
		return $sql;
	}

	/**
	 * 生成插入数据sql语句
	 */
	protected function getInsertSql($tableName,$data){
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		$sql = 'insert into `'.$tableName.'` (';

		$kStr = null;
		$vStr = null;
		if(!empty($data)){
			$count = 0;
			foreach ($data as $k => $v){
				if($count == 0){
					$kStr .= '`'.$k.'`';
					$vStr .= "'".$v."'";
				}else{
					$kStr .= ',`'.$k.'`';
					$vStr .= ",'".$v."'";
				}
				$count++;
			}
		}
		$sql .= $kStr.') values('.$vStr.')';
		return $sql;
	}

	/**
	 * 生成更新sql语句
	 */
	protected function getUpdataSql($tableName,$condition,$data){
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		
		$sql = 'update `'.$tableName.'` set ';
		if(empty($data)){
			throw new CException('无效的更新语句');
		}
		$count = 0;
		foreach ($data as $k=>$v){
			if($count == 0)
				$sql .= '`'.$k."` = '".$v."'";
			else 
				$sql .= ' , `'.$k."` = '".$v."' ";
			$count++;
		}
		if($condition instanceof Where && trim($condition->getCond ()) != ''){
			$sql .= ' where '.$condition->getCond();
		}elseif($condition&&is_string($condition)){
			$sql .= ' where ' . $condition;
		}else{
			throw new CException('无效的条件');
		}
		return $sql;
	}

	/**
	 * 生成删除sql语句
	 */
	protected function getDeltetSql($tableName,$condition){
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		$sql = 'delete from `'.$tableName.'` ';
		if($condition instanceof Where && trim($condition->getCond ()) != ''){
			$sql .= ' where '.$condition->getCond();
		}else{
			throw new CException('无效的条件');
		}

		return $sql;
	}

	/**
	 * 生成翻页器
	 */
	protected function generatePagener($data){
		$pagener = array();		
		$page_num_arr = array();
		
		for($i=1;$i<=$data['pageNums'];$i++){
			$page_num_arr[] = $i;
		}
		
		$nowPage = $data['currentPage'];
		if(count($page_num_arr) <= 5){
			$pagener = $page_num_arr;
		}else if($nowPage <= 3){
			for($i = 0;$i<=4;$i++){
				$pagener[] = $page_num_arr[$i];
			}
		}else if(($nowPage-1) >= 3 && isset($page_num_arr[$nowPage+1])){
			for($i = ($nowPage-3); $i <= ($nowPage + 1); $i++){
				$pagener[] = $page_num_arr[$i];
			}
		}else{
			for($i=($data['pageNums']-5);$i <= ($data['pageNums']-1); $i++){
				$pagener[] = $page_num_arr[$i];
			}
		}
		$data['linkList'] = $pagener;
		return $data;
	}
}