<?php
define('WEBPATH', dirname(__FILE__));
define('CONFIGPATH', WEBPATH . '/configs');
require CONFIGPATH . '/config.php';
require FRAMEWORKPATH . '/libs/lib_config.php';

// config配置文件目录
Swoole::$php->config->setPath(__DIR__.'/configs');

// apppath路径设置
Swoole::$php->setAppPath(APPPATH);
//controller目录设置
Swoole::$php->setControllerPath(WEBPATH."/controllers");
//view目录设置
Swoole::$php->tpl->template_dir = WEBPATH."/views";
//新增命名空间
Swoole\Loader::addNameSpace("DAO", APPPATH."/DAO");
//定义class文件夹位置
Swoole\Loader::addNameSpace("CLASSES", WEBPATH."/classes");
//默认访问地址
Swoole::$php->router(function(){
    return array('controller' => 'Manager', 'view' => 'add');
});

Swoole::$php->runMVC();
