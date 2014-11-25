<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
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
    
    
    public static function error_handler($code, $message, $file, $line)
    {
        if (0 == error_reporting())
        {
            return;
        }
        
        echo '<style>.exception-trace p{padding-top:0px;margin-top:0px} .exception-trace pre{background-color: #E0EBD3;padding-top:0px;margin-top:0px} .exception-trace-index{background-color: #BBDBF4; border-bottom: 1px #1188FF solid}</style>';
        echo '<div class="exception-trace">';
        //编码判断
        if(json_encode($message) == 'null'){
            $message=iconv("GBK", "UTF-8", $message); 
        }
        echo '<div class="exception-trace-index"><font color="red">系统错误</font>->'.$message.'</div>';
        echo '<pre>file:'.$file."-line:{$line}</pre>";
        echo '<div>';
    }
    
    public static function exception_handler($exception)
    {
        if(IVY_DEBUG){
            try
            {
                //编码判断
                $message = $exception->getMessage();
                if(json_encode($message) == 'null'){
                    $message=iconv("GBK", "UTF-8", $message); 
                }
                echo '<style>.exception-trace p{padding-top:0px;margin-top:0px} .exception-trace pre{background-color: #E0EBD3;padding-top:0px;margin-top:0px} .exception-trace-index{background-color: #BBDBF4; border-bottom: 1px #1188FF solid}</style>';
                echo '<div class="exception-trace">';
                echo '<b>Fatal error</b>:  未捕获的异常\'' . get_class($exception) . '\'  ';
                echo '<br>异常消息：<font color="red">'.$message.'</font><br>';
                echo 'Stack trace:<div>';
                foreach($exception->getTrace() as $key=>$t){
                    echo '<div class="exception-trace-index">trace->'.$key.'</div>';
                    echo '<pre>';
                    foreach($t as $k=>$v){
                        if($k=='args'){
                            echo "<p>args:";
                                var_export($v);
                            echo "</p>";
                        }else{
                            echo "<p>{$k}:{$v}</p>";
                        }
                    }
                    echo '</pre>';
                }
                echo '</div>';
                echo '异常抛出点：<b>' . $exception->getFile() . '</b> on line <b>' . $exception->getLine() . '</b><br>';
                echo '</div>';
                exit();
            }
            catch (Exception $e)
            {
                print get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine();exit();
            }
        }else{
            die('有未捕获的异常！');
        }
    }
    
    
    public static function shutdown_handler ()
    {
        // 资源操作 数据库连接 缓存 等处理
        if(session_id()!=='')
			@session_write_close();
        return true;
    }
    
 
}
set_error_handler(array("Ivy\core\CException", "error_handler"));
set_exception_handler(array("Ivy\core\CException", "exception_handler"));

register_shutdown_function (array ('Ivy\core\CException', 'shutdown_handler'));


