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
defined('DS') or define('DS',DIRECTORY_SEPARATOR);
defined('__ROOT__') or define('__ROOT__', dirname(__DIR__));                                    //定义网站根目录 D:\wwwroot\veecar   veecar为项目目录
defined('__PROTECTED__') or define('__PROTECTED__',__ROOT__.DS."protected");                    //定义项目文件根目录 D:\wwwroot\veecar\protected
defined('__RUNTIME__') or define('__RUNTIME__',__ROOT__.DS."runtime");
defined('IVY_PATH') or define('IVY_PATH',dirname(__FILE__));                              //定义框架根目录 D:\wwwroot\veecar\ivy\framework


defined('IVY_BEGIN_TIME') or define('IVY_BEGIN_TIME',microtime(true));				//开始时间
defined('IVY_DEBUG') or define('IVY_DEBUG',false);

// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

use Ivy\logging\CLogger;
use Ivy\core\CException;
class Ivy
{
    /**
     * @var Application
     */
	private static $_app;
	private static $_logger;

	//框架初始化代码
	public static function init()
	{
		require_once(IVY_PATH.DS.'core'.DS.'LoaderClass.php');//加载自动加载
		require_once(IVY_PATH.DS.'core'.DS.'CException.php');//加载异常处理
	}
	//创建应用实例
	public static function createApplication($config=null)
	{
		if($config===null)
			$config=__PROTECTED__.DS.'config.php';
		return new \Ivy\core\Application($config);
	}


    //创建应用实例
    public static function createConsole($config=null)
    {
        if($config===null)
            $config=__PROTECTED__.DS.'config.php';
        return new \Ivy\core\Console($config);
    }

    /**
     * 设置保存 application句柄
     * @param $app
     * @throws CException
     */
	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app=$app;
		else
			throw new CException('application can only be created once.');
	}

    /**
     * pplication句柄
     * @return Application
     */
	public static function app()
	{
		return self::$_app;
	}

    /**
     * 获取请求信息
     * @return \Ivy\core\Request|object
     */
	public static function request()
    {
        return \Ivy\core\Request::instance();
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
     * @param $msg
     * @param string $level
     * @param string $category
     */
	public static function log($msg,$level=CLogger::LEVEL_INFO,$category='application')
	{
		//不属于数据库性能分析的日志
		if(IVY_DEBUG && $level!==CLogger::LEVEL_PROFILE){
			$traces=debug_backtrace();
			$count=0;
			foreach($traces as $trace)
			{
				if(isset($trace['file'],$trace['line']) && strpos($trace['file'],IVY_PATH)!==0)
				{
					$msg.="\nin ".$trace['file'].' ('.$trace['line'].')';
					if(++$count>=CLogger::REPORT_TRACE_LEVEL)
						break;
				}
			}
		}
		self::logger()->log($msg,$level,$category);
	}

    /**
     * 扩展导入
     * @param $path
     * @param string $ext
     * @return mixed
     * @throws CException
     */
	public static function importExt($path,$ext=".php")
	{
		if(substr($path,0,1)=='/') $path=substr($path,1);
		
		$file_path=__PROTECTED__.DS.'extensions'.DS.$path.$ext;
		
		if(is_file($file_path))
			return include_once $file_path;
		else
			throw new CException("import $path error");
	}

    /**
     * 载入widget
     * @param $path
     * @param string $ext
     * @return mixed
     * @throws CException
     */
	public static function importWidget($path,$ext=".php")
	{
		if(substr($path,0,1)=='/') $path=substr($path,1);
		
		$file_path=__PROTECTED__.DS.'widgets'.DS.$path."Widget".$ext;
		
		if(is_file($file_path))
			return include_once $file_path;
		else
			throw new CException("import $path error");
	}

	/** 已下都移到Request对象中 **/
    /**
     * ajax判断
     * @return bool
     */
	public static function isAjax()
	{
	    return self::request()->isAjax();
	}

	/**
	 * Returns whether this is an Adobe Flash or Adobe Flex request.
	 * @return boolean 
	 * @since 1.1.11
	 */
	public static function isFlash()
	{
        return self::request()->isFlash();
	}

	/**
	 * 获取当前主机
	 * @return string 主机字符串
	 */
	public static function getHostInfo(){
        return self::request()->getHostInfo();
	}

    /**
     * 网站基础url（移除脚本路径）
     * @param bool $absolute
     * @return string
     * @throws CException
     */
	public static function getBaseUrl($absolute=false){
		return self::request()->getBaseUrl($absolute);
	}

    /**
     * 当前脚本url
     * @return mixed|string
     * @throws CException
     */
	public static function getScriptUrl(){
		return self::request()->getScriptUrl();
	}
}
Ivy::init();
defined('SITE_URL') or define('SITE_URL',Ivy::getBaseUrl(true));			//定义网站根url 绝对路径 http://www.test.com

function halt($var){
    var_dump($var);die;
}