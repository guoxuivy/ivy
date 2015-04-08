<?php
namespace admin;
use Ivy\core\ActiveRecord;
class CompanySupplier extends ActiveRecord
{
	public function tableName() {
		return 'company_supplier';
	}
	public function deleteAll($company_id){
		$company_id=(int)$company_id;
		if($company_id==0)
			return false;
		$db=$this->getDb();
		$sql="delete from ".$this->tableName()." where company_id={$company_id}";
		if($db->exec($sql))
		{
			return true;
		}
		return false;
	}
}