<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * DBException 级别的异常不要主动捕获
 */
namespace Ivy\db;
class DBException extends \Exception
{
	public function __construct($message=null,$code=0,$previous=null)
	{
		parent::__construct($message,$code,$previous);
		//判断是否有DB级别的异常，用于主动释放db连接
		\Ivy::app()->dbClose();
	}

}
