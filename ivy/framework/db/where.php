<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 * @comment 生成sql条件
 */
namespace Ivy\db;
use Ivy\core\CException;
class Where {
	
	public $condition = NULL;
	
	public function lessThen($key,$val){
		$this->checkCon($key, $val);
		$this->condition .= '`'.$key.'` < '."'".$val."'"; 
	}
	
	public function lessOrEqThen($key,$val){
		$this->checkCon($key, $val);
		$this->condition .= '`'.$key.'` <= '."'".$val."'"; 
	}
	
	public function moreThen($key,$val){
		$this->checkCon($key, $val);
		$this->condition .= '`'.$key.'` > '."'".$val."'"; 
	}
	
	public function moreOrEqThen($key,$val){
		$this->checkCon($key, $val);
		$this->condition .= '`'.$key.'` >= '."'".$val."'"; 
	}
	
	public function eqTo($key,$val){
		$this->checkCon($key, $val);
		$this->condition .= '`'.$key.'` = '."'".$val."'"; 
	}
	
	public function _and(){
		$this->condition .= ' and ';
	}
	
	public function _or(){
		$this->condition .= ' or ';
	}
	
	private function checkCon($key,$val){
		if(!isset($key) || !isset($val)){
			throw new CException('请输入正确的条件');
		}
	}
	public function getCond(){
		return $this->condition;
	}
}