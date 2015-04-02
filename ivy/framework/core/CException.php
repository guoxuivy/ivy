<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
use Ivy\logging\CLogger;
class CException extends \Exception
{
	/**
	 * Constructor.
	 * @param integer $status HTTP status code, such as 404, 500, etc.
	 * @param string $message error message
	 * @param integer $code error code
	 */
	public function __construct($message=null,$code=0)
	{
		parent::__construct($message,$code);
	}

	/**
	 * 将整数拆解为2的幂和
	 * @param  [type] $num [description]
	 * @return [type]      [description]
	 */
	public static function pow_log($num){
		$res = array();
		while($num>0){
			$re = pow(2, floor(log($num,2)));
			$num -= $re;
			$res[] = $re;
		}
		return $res;

	}



	/**
	 * 自定义系统错误处理 启用后 仅error_reporting = 0 生效
	 * @param  [type] $code    [description]
	 * @param  [type] $message [description]
	 * @param  [type] $file    [description]
	 * @param  [type] $line    [description]
	 * @return [type]          [description]
	 */
	public static function error_handler($code, $message, $file, $line)
	{
		if (0 == error_reporting()) return;
		$reporting_arr=self::pow_log(error_reporting());//当前警告级别拆分
		if(!in_array($code, $reporting_arr)) return;//不在开启的错误级别中

		//编码判断
		if(json_encode($message) == 'null'){
			$message=iconv("GBK", "UTF-8", $message); 
		}
		// if(IVY_DEBUG){
		// 	echo '<style>.exception-trace p{padding-top:0px;margin-top:0px} .exception-trace pre{background-color: #E0EBD3;padding-top:0px;margin-top:0px} .exception-trace-index{background-color: #BBDBF4; border-bottom: 1px #1188FF solid}</style>';
		// 	echo '<div class="exception-trace">';
		// 	echo '<div class="exception-trace-index"><font color="red">系统错误</font>->'.$message.'</div>';
		// 	echo '<pre>file:'.$file."-line:{$line}</pre>";
		// 	echo '<div>';
		// 	return;
		// }
		
		switch ($code) {
			case 2:
				$level = CLogger::LEVEL_WARNING;
				break;
			case 8:
				$level = CLogger::LEVEL_NOTICE;
				break;
			default:
				$level = CLogger::LEVEL_ERROR;
				break;
		}
		\Ivy::log("[system-level-$code] [".$message.'] file:'.$file.'->line:'.$line,$level);
		
	}
	/**
	 * 异常框架捕获
	 * @param  [type] $exception [description]
	 * @return [type]            [description]
	 */
	public static function exception_handler($exception)
	{
		//编码判断
		$message = $exception->getMessage();
		if(json_encode($message) == 'null'){
			$message=iconv("GBK", "UTF-8", $message); 
		}
		$str = $str_log = '';
		try {
			$str.= '<style>.exception-trace p{padding-top:0px;margin-top:0px} .exception-trace pre{background-color: #E0EBD3;padding-top:0px;margin-top:0px} .exception-trace-index{background-color: #BBDBF4; border-bottom: 1px #1188FF solid}</style>';
			$str.= '<div class="exception-trace">';
			$str.= '<b>Fatal error</b>:  未捕获的异常\'' . get_class($exception) . '\'  ';
			$str_log.= "\n<!--Fatal error-->\n未捕获的异常" . get_class($exception) . "  \n";
			$str.= '<br>异常消息：<font color="red">'.$message.'</font><br>';
			$str_log.= "异常消息：".$message."  \n";
			$str.= 'Stack trace:<div>';
			foreach($exception->getTrace() as $key=>$t){
				$str.= '<div class="exception-trace-index">trace->'.$key.'</div>';
				$str.= '<pre>';
				foreach($t as $k=>$v){
					if($k=='args'){
						$str.= "<p>args:";
							$str.=var_export($v,true);
							$str_log.= "args:".var_export($v,true)."  \n";
						$str.= "</p>";
					}else{
					   $str.= "<p>{$k}:{$v}</p>";
					   $str_log.= "{$k}:{$v}  \n";
					}
					
				}
				$str.= '</pre>';
			}
			$str.= '</div>';
			$str.= '异常抛出点：<b>' . $exception->getFile() . '</b> on line <b>' . $exception->getLine() . '</b><br>';
			$str_log.= '异常抛出点：' . $exception->getFile() . ' on line ' . $exception->getLine() ."\n<!--Fatal error end-->\n";
			$str.= '</div>';
		} 
		catch (\Exception $e) {
			$str.= get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine();
			$str_log.= get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine()."\n";
		}
		if(IVY_DEBUG){
			echo $str;die;
		}else{
			\Ivy::log($str_log,CLogger::LEVEL_TRACE);
			if(isset(\Ivy::app()->config['errorHandler']['errorAction'])){
				\Ivy::app()->hook(\Ivy::app()->config['errorHandler']['errorAction'],array('code'=>404,'msg'=>$message));die();
			}
			die('发生错误！');
		}
	}


	/**
	 * 所有代码执行完毕后处理 包括非正常结束和正常结束
	 * @return [type] [description]
	 */
	public static function shutdown_handler ()
	{
		//日志写入
		\Ivy::logger()->flush();
		// 资源操作 数据库连接 缓存 等处理
		if(session_id()!=='')
			@session_write_close();
		return true;
	}

}
set_error_handler(array("Ivy\core\CException", "error_handler")); //是否启用框架自定义错误处理
set_exception_handler(array("Ivy\core\CException", "exception_handler"));
register_shutdown_function (array ('Ivy\core\CException', 'shutdown_handler'));