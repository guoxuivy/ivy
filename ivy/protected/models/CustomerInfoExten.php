<?php
/**
 * @Author: K
 * @Date:   2015-03-31 15:11:42
 * @Last Modified by:   K
 * @Last Modified time: 2015-03-31 15:14:07
 */

/**
 * 客户基本信息
 */
use Ivy\core\ActiveRecord;
class CustomerInfoExten extends ActiveRecord
{
	public function tableName() {
		return 'customer_info_exten';
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
		return $this->save();
	}
}