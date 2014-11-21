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
 
defined('__ROOT__') or define('__ROOT__', dirname(__DIR__));                                    //定义网站根目录 D:\wwwroot
defined('__PROTECTED__') or define('__PROTECTED__',__ROOT__.DIRECTORY_SEPARATOR."protected");   //定义项目文件根目录 D:\wwwroot..protected
defined('SITE_URL') or define('SITE_URL', dirname($_SERVER['SCRIPT_NAME']));                    //定义访问地址  /ivy
defined('IVY_PATH') or define('IVY_PATH',dirname(__FILE__));                                    //定义框架根目录 D:\wwwroot\ivy\framework

use Ivy\core\Application;
class Ivy
{
    //框架初始化代码
    public static function init()
	{
        require_once(IVY_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'LoaderClass.php');//加载自动加载
        require_once(IVY_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'CException.php');//加载异常处理
        Ivy::quotes_gpc();
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
    
    public static function import($uri){
        require_once($uri);
    }
    
    //安全过滤
    public static function quotes_gpc()
	{
		if (!get_magic_quotes_gpc()){
            !empty($_POST)     && Ivy::add_s($_POST);
            !empty($_GET)     && Ivy::add_s($_GET);
            !empty($_COOKIE) && Ivy::add_s($_COOKIE);
            !empty($_REQUEST) && Ivy::add_s($_REQUEST);
        }
        !empty($_FILES) && Ivy::add_s($_FILES);
	}
    
    public static function add_s(&$array){
        if (is_array($array)){
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $array[$key] = addslashes($value);
                } else {
                    Ivy::add_s($array[$key]);
                }
            }
        }
    }
    
}


Ivy::init();


