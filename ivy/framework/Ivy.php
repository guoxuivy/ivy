<?php
/**
 * Ivy class file.
 *
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */

header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Shanghai');
 
defined('__ROOT__') or define('__ROOT__', dirname(__DIR__));           //定义网站根目录 D:\wwwroot
defined('__PROTECTED__') or define('__PROTECTED__',__ROOT__.DIRECTORY_SEPARATOR."protected");           //定义网站根目录 D:\wwwroot
defined('SITE_URL') or define('SITE_URL', dirname($_SERVER['SCRIPT_NAME']));    //定义访问地址  /ivy
defined('IVY_PATH') or define('IVY_PATH',dirname(__FILE__));                    //定义框架根目录 D:\wwwroot\ivy\framework

use Ivy\core\Application;
class Ivy
{
    //框架初始化代码
    public static function init()
	{
        require_once(IVY_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'LoaderClass.php');//加载自动加载
        require_once(IVY_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'CException.php');//加载异常处理
	}
    //创建应用实例
	public static function createApplication()
	{
        $config=__PROTECTED__.DIRECTORY_SEPARATOR.'config.php';
		return Application::init($config);
	}
    //获取应用实例句柄
    public static function app()
	{
		return Application::init();
	}
    
}
Ivy::init();


