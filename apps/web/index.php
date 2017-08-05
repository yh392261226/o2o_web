<?php
define('WEBPATH', dirname(__FILE__));
require WEBPATH . '/configs/config.php';
require FRAMEWORKPATH . '/libs/lib_config.php';
Swoole::$php->setAppPath(APPPATH);
//Swoole::$php->runMVC();
Swoole::$php->runConsole();
