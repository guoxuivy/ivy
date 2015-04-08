<?php
namespace admin;
use Ivy\core\ActiveRecord;
class CompanyUser extends ActiveRecord
{
	public function tableName() {
		return 'company_user';
	}
}