<?php
/**
 * @Author: K
 * @Date:   2015-03-30 18:11:28
 * @Last Modified by:   K
 * @Last Modified time: 2015-04-01 17:27:39
 */
/**
 * 项目配方详情
 */
use Ivy\core\ActiveRecord;
class ProjectFormulaCompose extends ActiveRecord
{
	public function tableName() {
		return 'project_formula_compose';
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
}