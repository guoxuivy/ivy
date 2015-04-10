<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * 页面小插件
 * 最好不使用布局文件 当前主控制器路由对象注入
 */
namespace Ivy\core;
abstract class Widget extends Controller {
	public function __construct() {
		//注入当前主控制器路由
		$this->attachBehavior(\Ivy::app()->_route);
		$this->init();//用于重写
	}

	/**
	 * widget必须包含run()方法，由于低php版本反射形参限制 无法定义为抽象方法
	 */
	//abstract function run();
}
