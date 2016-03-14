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
use Ivy\core\CException;
class DBException extends CException
{
	public function __construct($message=null,$code=0,$previous=null)
	{
		parent::__construct($message,$code,$previous);
	}

}
