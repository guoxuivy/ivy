<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * 页面小插件
 * 不支持布局文件 无路参数
 */
namespace Ivy\core;
abstract class Widget extends Controller {
	public function __construct() {
		//当前的路由对象
		$this->route = new Route();
	}
	abstract function run();
}