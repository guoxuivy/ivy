<?php
/**
 * Ivy class file.
 *
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Shanghai');
 
defined('__ROOT__') or define('__ROOT__', dirname(__DIR__));                                    //定义网站根目录 D:\wwwroot\veecar   veecar为项目目录
defined('__PROTECTED__') or define('__PROTECTED__',__ROOT__.DIRECTORY_SEPARATOR."protected");   //定义项目文件根目录 D:\wwwroot\veecar\protected
defined('SITE_URL') or define('SITE_URL', dirname($_SERVER['SCRIPT_NAME']));                    //定义访问相对路径  /veecar
defined('IVY_PATH') or define('IVY_PATH',dirname(__FILE__));                                    //定义框架根目录 D:\wwwroot\veecar\ivy\framework
defined('IVY_BEGIN_TIME') or define('IVY_BEGIN_TIME',microtime(true));
defined('IVY_DEBUG') or define('IVY_DEBUG',false);  
use Ivy\core\Application;
use Ivy\logging\CLogger;
class Ivy
{
	private static $_app;
	private static $_logger;

	//框架初始化代码
	public static function init()
	{
		require_once(IVY_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'LoaderClass.php');//加载自动加载
		require_once(IVY_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'CException.php');//加载异常处理
		Ivy::quotes_gpc();
	}
	//创建应用实例
	public static function createApplication($config=null)
	{
		if($config===null)
			$config=__PROTECTED__.DIRECTORY_SEPARATOR.'config.php';
		return new Application($config);
	}
	/**
	 * 设置保存 application句柄
	 * @param [type] $app [description]
	 */
	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app=$app;
		else
			throw new CException('application can only be created once.');
	}
	/**
	* application句柄
	**/
	public static function app()
	{
		return self::$_app;
	}

	/**
	* 日志句柄
	**/
	public static function logger()
	{
		if(self::$_logger===null){
		   self::$_logger=new CLogger;
		}
		return self::$_logger;
	}
	/**
	* 快速日志写入
	**/
	public static function log($msg,$level=CLogger::LEVEL_INFO,$category='application')
	{
		if($level===CLogger::LEVEL_PROFILE)
		{
			$traces=debug_backtrace();
			$count=0;
			foreach($traces as $trace)
			{
				if(isset($trace['file'],$trace['line']))
				{
					$msg.=" in->".$trace['file'].' ('.$trace['line'].")";
					if(++$count>=CLogger::REPORT_TRACE_LEVEL)
						break;
				}
			}
		}
		self::logger()->log($msg,$level,$category);
	}


	/**
	 * http 提交安全转义
	 * @return [type] [description]
	 */
	public static function quotes_gpc()
	{
		!empty($_POST)    && Ivy::add_s($_POST);
		!empty($_GET)     && Ivy::add_s($_GET);
		!empty($_COOKIE)  && Ivy::add_s($_COOKIE);
	}
	/**
	 * 递归转义
	 * @param [type] &$array [description]
	 */
	public static function add_s(&$array)
	{
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


	/**
	 * 扩展导入
	 * @param [type] &$array [description]
	 */
	public static function importExt($path,$ext=".php")
	{
		if(substr($path,0,1)=='/') $path=substr($path,1);
		
		$file_path=__PROTECTED__.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$path.$ext;
		
		if(is_file($file_path))
			return include_once $file_path;
		else
			throw new CException("import $path error");

	}
}


Ivy::init();