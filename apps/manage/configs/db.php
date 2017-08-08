<?php
$db['master'] = array(
    'type'       => Swoole\Database::TYPE_MYSQLi,
    'host'       => "127.0.0.1",
    'port'       => 3306,
    'dbms'       => 'mysql',
    'engine'     => 'MyISAM',
    'user'       => "manager",
    'passwd'     => "manager",
    'name'       => "o2o_hire",
    'charset'    => "utf8",
    'setname'    => true,
    'persistent' => false, //MySQL长连接
    'use_proxy'  => false,  //启动读写分离Proxy
    'slaves'     => array(
        //array('host' => '127.0.0.1', 'port' => '3309', 'weight' => 98,),
            ),
        );

Swoole::$php->config['db'] = $db;
