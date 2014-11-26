<?php
/**
 * @author ivy;
 */
return array (
		//pdo数据库配置
		'db_pdo' => array (
			'dsn' => 'mysql:dbname=veecar;host=127.0.0.1',
			'user' => 'root',
			'password' => ''
		),
        //支持memcache集群
        'memcache'=>array(
            "127.0.0.1:11211",
            "192.168.1.202:11211"
        ),
		
		//默认路由
		'route' => array(
			'controller' =>'index',
			'action'	=> 'index'
		),
		
);