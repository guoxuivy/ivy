<?php
/**
 * @Author: K
 * @Date:   2015-03-31 12:01:41
 * @Last Modified by:   K
 * @Last Modified time: 2015-04-02 18:32:58
 */
/**
 * 商品信息
 */
use Ivy\core\ActiveRecord;
class ProductInfo extends ActiveRecord
{
	public function tableName() {
		return 'product_info';
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
		$this->comp_id=$company_info['id'];
		if ($this->save()) {
			if (isset($data['formula'])) {//配方保存
				foreach ($data['formula'] as $value) {
					ProjectFormula::model()->saveData($value);
				}
			}
			return true;
		}
		return false;
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
	 * 获取状态
	 * @param  $Pk 
	 * @return 
	 */
	public static function getStatus($Pk=null){
		$Array = array(
			'1'		=> '正常',
			'-1'	=> '删除',
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
	 * 获取状态
	 * @param  $Pk 
	 * @return 
	 */
	public static function getType($Pk=null){
		$Array = array(
			'1'		=> '院装',
			'2'		=> '家装',
			'3'		=> '通用',
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