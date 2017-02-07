<?php
/**
 * CProfileLogRoute class file.
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0
 */
namespace Ivy\logging;
use Ivy\core\CException;
class CProfileLogRoute extends CLogRoute
{
	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		$this->levels=CLogger::LEVEL_PROFILE;
	}

	//重写搜集器
	public function collectLogs($logger, $processLogs=false)
	{
		$logs=$logger->getProfilingResults();
		$this->logs=empty($this->logs) ? $logs : array_merge($this->logs,$logs);
		//if($processLogs && !empty($this->logs))
		if(IVY_DEBUG && !\Ivy::app()->noProfile && !empty($this->logs)){
			$this->processLogs($this->logs);
			$this->logs=array();
		}
	}

	/**
	 * 数据库sql执行效率分析日志
	 * @param  boolean $dumpLogs [description]
	 * @return [type]            [description]
	 */
	public function processLogs($logs)
	{
		//搜集profile信息
		$list=array();
		foreach ($logs as $value) {
			$tmp['sql']=$value[0];
			$tmp['time']=round($value[2],4);
			$this->push_profile($tmp,$list);
		}
    	$this->render('profile-summary',$list);
		
	}

	/**
	 * 相同sql合并处理
	 * @param  [type] $tmp   [description]
	 * @param  [type] &$list [description]
	 * @return [type]        [description]
	 */
	public function push_profile($tmp,&$list){
		$sql=trim($tmp['sql']);
		$md5sql=md5(preg_replace("/\s/","",$sql));
		if(isset($list[$md5sql])){
			$list[$md5sql]['num']++;
			$list[$md5sql]['time'].=",".$tmp['time'];
			$list[$md5sql]['all_time']+=$tmp['time'];
			$list[$md5sql]['avg_time'] = round($list[$md5sql]['all_time']/$list[$md5sql]['num'],4);
		}else{
			$list[$md5sql]=array('key'=>$md5sql,'sql'=>$sql,'time'=>$tmp['time'],'avg_time'=>$tmp['time'],'all_time'=>$tmp['time'],'num'=>1);
		}
	}

	/**
	 * 渲染输出
	 * @param  [type] $view [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	protected function render($view,$data)
	{
		if(\Ivy::isAjax() || \Ivy::isFlash())
			return;
		$viewFile=IVY_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.'.php';
		include($viewFile);
	}
}