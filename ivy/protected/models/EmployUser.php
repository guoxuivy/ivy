<?php
use Ivy\core\ActiveRecord;
class EmployUser extends ActiveRecord
{
	public function tableName() {
		return 'employ_user';
	}
}