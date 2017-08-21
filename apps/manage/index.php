<?php
define('WEBPATH', dirname(__FILE__));

require WEBPATH . '/configs/config.php';
require CONFIGPATH . '/menu.php';
require FRAMEWORKPATH . '/libs/lib_config.php';
require APPPATH.'/public/public.php';

Swoole::$php->config->setPath(WEBPATH.'/configs');          // config配置文件目录
Swoole::$php->config['menu'] = $menu;                       // menu 配置
Swoole::$php->setAppPath(APPPATH);                          // apppath路径设置
Swoole::$php->setControllerPath(WEBPATH."/controllers");    //controller目录设置
Swoole::$php->tpl->template_dir = WEBPATH."/views";         //view目录设置
Swoole::$php->template_dir = WEBPATH."/views";              //view目录设置
Swoole\Loader::addNameSpace("MDAO", WEBPATH."/dao");        //dao层命名空间
Swoole\Loader::addNameSpace('MMODEL', APPPATH . '/modelbase'); //modelbase命名空间
// Swoole\Loader::addNameSpace("MANAGECONTROLLERS", WEBPATH."/controllers");   //新增命名空间
Swoole\Loader::addNameSpace("CLASSES", WEBPATH."/classes"); //定义class文件夹位置
Swoole::$default_controller = array('controller' => 'index', 'view' => 'index'); //默认访问的控制器及方法
Swoole::$php->runMVC();
