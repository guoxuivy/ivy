<?php
/**
 * @Author: K
 * @Date:   2015-03-30 16:23:42
 * @Last Modified by:   K
 * @Last Modified time: 2015-04-02 18:33:07
 */
/**
 * 项目配方
 */
use Ivy\core\ActiveRecord;
class ProjectFormula extends ActiveRecord
{
	public function tableName() {
		return 'project_formula';
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
			if (isset($data['compose'])) {//配方详情保存
				foreach ($data['compose'] as $value) {
					ProjectFormulaCompose::model()->saveData($value);
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
	 *  更新默认 
	 * @return 
	 */
	public function updateDefault(){
		if($this->project_id)
		{
			$this->updateAll(array('is_default'=>'false'),"project_id={$this->project_id} AND is_default='true'");
			$this->is_default='true';
			return $this->update();
		}
		return false;
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