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
        echo '<b>Fatal error</b>:  系统错误-\'' . $message . '\' - '.$file."-line:{$line}";
        echo '<div>';
        //throw new CException($message, 0, $code, $file, $line);
    }
    
    public static function exception_handler($exception)
    {
        if(IVY_DEBUG){
            try
            {
                echo '<style>.exception-trace p{padding-top:0px;margin-top:0px} .exception-trace pre{background-color: #E0EBD3;padding-top:0px;margin-top:0px} .exception-trace-index{background-color: #BBDBF4; border-bottom: 1px #1188FF solid}</style>';
                echo '<div class="exception-trace">';
                echo '<b>Fatal error</b>:  未捕获的异常\'' . get_class($exception) . '\'  ';
                echo '<br>异常消息：<font color="red">'.$exception->getMessage() . '</font><br>';
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
            die('发生错误！');
        }
    }
    

    

}



set_error_handler(array("Ivy\core\CException", "error_handler"));
set_exception_handler(array("Ivy\core\CException", "exception_handler"));

