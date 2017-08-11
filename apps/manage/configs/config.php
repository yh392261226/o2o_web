<?php
/**
 * 配置文件
 *
 *
 *
 *
 */
if (!defined('WEBPATH')) {
    exit('WEBPATH does not defined !!!');
}

define('FRAMEWORKPATH', WEBPATH . '/../../framework');
define('APPPATH', WEBPATH . '/..');
define('STATICPATH', WEBPATH . '/../../static');

define('STATICURL', 'http://devstatic.gangjianwang.com');

define('MANAGEURL', STATICURL . '/manager/');
define('UPLOADURL', STATICURL . '/uploads/');
define('HOSTURL', 'http://' . $_SERVER['HTTP_HOST']);
