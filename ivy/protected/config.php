<?php
/**
 * @author ivy;
 */
return array (
		//pdo数据库配置
		'db_pdo' => array (
				'dsn' => 'mysql:dbname=ivy;host=127.0.0.1',
				'user' => 'root',
				'password' => ''
		),
		
		//默认路由
		'route' => array(
			'controller' =>'index',
			'action'	=> 'index'
		),
		
);
