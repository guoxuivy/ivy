<?php
/**
 * @Author: sam
 */
/**
 * 项目、产品分类模型
 */
use Ivy\core\ActiveRecord;
class ProCate extends ActiveRecord
{
	public function tableName() {
		return 'pro_cate';
	}

	/**
	 * 商品顶级分类固定
	 * @param  string $id [description]
	 * @return [type]     [description]
	 */
	public static function getProductTopCate($id = ''){
		static $arr=null;
		if(is_null($arr)){
			$res = self::model()->field('id,name')->where('comp_id=0 and level=1 and type=2')->findAll();
			foreach ($res as $key => $value) {
				$arr[$value['id']]=$value['name'];
			}
		}
		// $arr = array(
		// 	'1' => '可售商品',
		// 	'2' => '礼品赠品',
		// 	'3' => '油粉胶膜消耗品',
		// 	'4' => '其他',
		// );
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		} else {
			return false;
		}

	}

	/**
	 * 可售商品类型固定
	 * @param  string $id [description]
	 * @return [type]     [description]
	 */
	public static function getProductType($id = ''){
		$arr = array(
			'1' => '院装',
			'2' => '家装',
			'3' => '通用',
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		} else {
			return false;
		}
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
		if(array_key_exists($Pk, $Array)) {
			return $Array[$Pk];
		} else {
			return false;
		}
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

}