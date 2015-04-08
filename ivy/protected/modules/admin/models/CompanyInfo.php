<?php
namespace admin;
use Ivy\core\ActiveRecord;
class CompanyInfo extends ActiveRecord
{
	public function tableName() {
		return 'company_info';
	}
	public function setStep($num)
	{
		$company_info = \Ivy::app()->user->getState('company_info');
		$id=$company_info['id'];
		$model=\admin\CompanyInfo::model()->findByPk($id);
		if ($num==($model->init_step+1)) 
			$model->init_step=$num;
		if($company_info['id']==null) 
			return false;
		return $model->save();
	}
}