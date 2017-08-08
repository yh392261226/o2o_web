<?php
define('WEBPATH', dirname(__FILE__));
define('CONFIGPATH', WEBPATH . '/configs');
require CONFIGPATH . '/config.php';
require FRAMEWORKPATH . '/libs/lib_config.php';
require CONFIGPATH . '/db.php';
Swoole::$php->config['db'] = $db;
Swoole::$php->setAppPath(WEBPATH);
Swoole::$php->runMVC();
