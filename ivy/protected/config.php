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
        'memcache' => array(
            "127.0.0.1:11211",
        ),
		
		//默认路由
		'route' => array(
			'controller' =>'index',
			'action'	=> 'index'
		),
		//权限管理配置
		'rbac' => array(
	        'userclass' => "\\admin\\UserModel", //default: User      对应用户的model
	        'userid' => 'id', //default: userid     用户表标识位对应字段
	        'username' => 'account', //default:username  用户表中用户名对应字段
		),
		//友好错误显示页面
        'errorHandler' => array(
            'errorAction' => 'error/index',
        ),

        /******************************以上为系统框架配置**************************************/
		
);