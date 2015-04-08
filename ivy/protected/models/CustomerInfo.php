<?php
/**
 * @Author: K
 * @Date:   2015-03-31 13:42:15
 * @Last Modified by:   K
 * @Last Modified time: 2015-04-01 17:27:04
 */
/**
 * 客户基本信息
 */
use Ivy\core\ActiveRecord;
class CustomerInfo extends ActiveRecord
{
	public function tableName() {
		return 'customer_info';
	}
	/**
	 * 数据保存
	 * @param  array $Data  [description]
	 * @param  object $model 
	 * @return 
	 */
	public function saveData($data)
	{
		$this->attributes=$data;
		$company_info=Ivy::app()->user->getState('company_info');
		$this->company_id=$company_info['id'];
		return $this->save();
	}
	/**
	 *  更新状态(删除)
	 * @param  integer $status 
	 * @return 
	 */
	public function updateStatus($status=-1){
		$this->status=$status;
		return $this->update();
	}
	/**
	 *  更新最后到店时间
	 * @param  integer $status 
	 * @return 
	 */
	public function updateLastTime(){
		$this->last_time=time();
		return $this->update();
	}
	/**
	 * 获取性别
	 * @param  $Pk 
	 * @return 
	 */
	public static function getSex($Pk=null){
		$Array = array(
			'男' => '男',
			'女' => '女',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取属相
	 * @param  $Pk 
	 * @return 
	 */
	public static function getZhZodiac($Pk=null){
		$Array = array(
			'鼠' => '鼠',
			'牛' => '牛',
			'虎' => '虎',
			'兔' => '兔',
			'龙' => '龙',
			'蛇' => '蛇',
			'马' => '马',
			'羊' => '羊',
			'猴' => '猴',
			'鸡' => '鸡',
			'狗' => '狗',
			'猪' => '猪',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取星座 '','','','','','','','','','','',''
	 * @param  $Pk 
	 * @return 
	 */
	public static function getZodiak($Pk=null){
		$Array = array(
			'水瓶座' => '水瓶座',
			'双鱼座' => '双鱼座',
			'白羊座' => '白羊座',
			'金牛座' => '金牛座',
			'双子座' => '双子座',
			'巨蟹座' => '巨蟹座',
			'狮子座' => '狮子座',
			'处女座' => '处女座',
			'天秤座' => '天秤座',
			'天蝎座' => '天蝎座',
			'射手座' => '射手座',
			'摩羯座' => '摩羯座',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取职业类型(1全职,2兼职,3退休,4家庭妇女5.自由职业6.企业主)
	 * @param  $Pk 
	 * @return 
	 */
	public static function getProfessionType($Pk=null){
		$Array = array(
			'1' => '全职',
			'2' => '兼职',
			'3' => '退休',
			'4' => '家庭妇女',
			'5' => '自由职业',
			'6' => '企业主',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取婚姻状况(1未婚,2已婚,3离异,4丧偶)
	 * @param  $Pk 
	 * @return 
	 */
	public static function getMarriage($Pk=null){
		$Array = array(
			'1' => '未婚',
			'2' => '已婚',
			'3' => '离异',
			'4' => '丧偶',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取客户来源(1.自上门.2.外联3.嘉宾4.拓客5.广告6.其他7.系统会员)
	 * @param  $Pk 
	 * @return 
	 */
	public static function getComeType($Pk=null){
		$Array = array(
			'1' => '自上门',
			'2' => '外联',
			'3' => '嘉宾',
			'4' => '拓客',
			'5' => '广告',
			'6' => '其他',
			'7' => '系统会员',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取客户类型(ABCD...)
	 * @param  $Pk 
	 * @return 
	 */
	public static function getCuType($Pk=null){
		$Array = array(
			'1' => 'A',
			'2' => 'B',
			'3' => 'C',
			'4' => 'D',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
	/**
	 * 获取客户状态（活跃1、久档2、死档3、无效-1）
	 * @param  $Pk 
	 * @return 
	 */
	public static function getStatus($Pk=null){
		$Array = array(
			'1'  => '活跃',
			'2'  => '久档',
			'3'  => '死档',
			'-1' => '无效',
		);
		if($Pk === null) {
			return $Array;
		}
		else {
			if(array_key_exists($Pk, $Array)) {
				return $Array[$Pk];
			}
			else {
				return false;
			}
		}
	}
}