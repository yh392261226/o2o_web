<?php
define('WEBPATH', dirname(__FILE__));
date_default_timezone_set('Asia/Shanghai');
require WEBPATH . '/configs/config.php';
require CONFIGPATH . '/menu.php';
require CONFIGPATH . '/fields_type.php';
require FRAMEWORKPATH . '/libs/lib_config.php';
require APPPATH.'/public/public.php';

Swoole::$php->config->setPath(WEBPATH.'/configs');          // config配置文件目录
Swoole::$php->config['fields_type'] = $fields_type;         // 数据库字段类型
Swoole::$php->setAppPath(APPPATH);                          // apppath路径设置
Swoole::$php->setControllerPath(WEBPATH."/controllers");    //controller目录设置
Swoole\Loader::addNameSpace("WDAO", WEBPATH."/dao");        //dao层命名空间
Swoole\Loader::addNameSpace('MMODEL', APPPATH . '/modelbase'); //modelbase命名空间
Swoole\Loader::addNameSpace('MDAOBASE', APPPATH . '/daobase'); //daobase命名空间
Swoole\Loader::addNameSpace("MLIB", APPPATH."/../libraries");   //第三方类库
Swoole\Loader::addNameSpace("CLASSES", WEBPATH."/classes"); //定义class文件夹位置
Swoole::$default_controller = array('controller' => 'index', 'view' => 'index'); //默认访问的控制器及方法
Swoole::$php->runMVC();