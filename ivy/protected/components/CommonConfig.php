<?php
/**
 * 通用配置
 * @author Chris
 *
 */
class CommonConfig
{
	/**
	 * 获取项目分类的数组
	 * @param string $id
	 * @return array
	 */
	public static function getProjectLevel($id = '') {
		$arr = array(
			'1' => '面部项目',
			'2' => '身体项目',
			'3' => '合作项目',
			'4' => '其他类型项目',
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}
	
	/**
	 * 获取部门的数组
	 * @param string $id
	 * @return array
	 */
	public static function getDeptArr($id = '') {
		$arr = array(
			'1' => '总店经理室',
			'2' => '财务部',
			'3' => '项目部',
			'4' => '采购部',
			'6' => '运营部',
			'7' => '人力资源部',
			'8' => '培训部',
			'9' => '市场部',
			'10' => '总务部',
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}
	
	/**
	 * 获取部门的数组
	 * @param string $id
	 * @return array
	 */
	public static function getPositionArr($id = '') {
		$arr = array(
			'1' => array(
				'3' => '总经理',
				'5' => '总监',
			),
			'2' => array(
				'6' => '财务经理',
				'8' => '会计',
			),
			'100' => array( //门店的职位
				'12' => '门店经理',
				'13' => '美容顾问',
				'14' => '美容师',
				'15' => '门店前台',
			),
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}
	
	/**
	 * 获取高级版部门的数组
	 * @param string $id
	 * @return array
	 */
	public static function getSeniorPositionArr($id = '') {
		$arr = array(
			'6' => array(
				'6' => '运营部经理',
				'0' => '运营部职员',
			),
			'8' => array(
				'6' => '培训部经理',
				'0' => '培训部职员',
			),
			'9' => array(
				'6' => '市场部经理',
				'0' => '市场部职员',
			),
			'3' => array(
				'6' => '项目部经理',
				'0' => '项目部职员',
			),
			'7' => array(
				'6' => '人事部经理',
				'0' => '人事部职员',
			),
			'4' => array(
				'6' => '采购部经理',
				'0' => '采购部职员',
			),
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}
	
	/**
	 * 获取商品分类的数组
	 * @param string $id
	 * @return array
	 */
	public static function getProductLevel($id = '') {
		$arr = array(
			'1' => '院装产品',
			'2' => '家居产品',
			'3' => '通用产品',
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}
	
	/**
	 * 获取商品单位的数组
	 * @param string $id
	 * @return array
	 */
	public static function getProductUnit($id = '') {
		$arr = array(
			'1' => '瓶',
			'2' => '包',
			'3' => '袋',
			'4' => '盒',
			'5' => '片',
			'6' => '罐',
			'7' => '套',
			'8' => '件',
			'9' => '个',
			'10' => '台',
			'11' => '支',
			'12' => '条',
			'13' => '双',
			'14' => '张',
			'15' => '根',
			'16' => '架',
			'17' => '只',
			'18' => '部',
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}	
	
	/**
	 * 获取初始化的步骤数组
	 * @param string $id
	 * @return array
	 */
	public static function getInitStep($id = '') {
		$arr = array(
			'1' => '部门管理',
			'2' => '门店管理',
			'3' => '房间管理',
			'4' => '会员卡管理',
			'5' => '供应商管理',
			'6' => '员工管理',
			'7' => '商品管理',
			'8' => '项目管理',
		);
		if($id === '') {
			return $arr;
		}
		if(array_key_exists($id, $arr)) {
			return $arr[$id];
		}
		else {
			return false;
		}
	}
	
		
}