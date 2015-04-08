<?php
/**
 * @author ivy;
 */
return array (
		//pdo数据库配置
		'db_pdo' => array (
			//'dsn' => 'mysql:dbname=beauty_admin;host=192.168.1.202:13306',
			'dsn' => 'mysql:dbname=platform;host=192.168.1.202:13306',
			'user' => 'root',
			'password' => 'mysqladmin56',
		),
        //支持memcache集群
        'memcache'=>array(
            "127.0.0.1:11211",
            "192.168.1.202:11211",
        ),
		
		//默认路由
		'route' => array(
			'controller' =>'index',
			'action'	=> 'index',
		),
		//友好错误显示页面
        'errorHandler'=>array(
            'errorAction'=>'error/index',
        ),
		//开启表单令牌
        'token'=>true,

        /******************************以上为系统框架配置**************************************/

		
		
);