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

define('CONFIGPATH', WEBPATH . '/configs');
define('FRAMEWORKPATH', WEBPATH . '/../../framework');
define('APPPATH', WEBPATH . '/..');
define('STATICPATH', WEBPATH . '/../../static');
define('STATICURL', 'http://devstatic.gangjianwang.com');
define('MANAGEURL', STATICURL . '/manager/');//定义后台静态文件路径
// define('UPLOADURL', STATICURL . '/uploads/');//定义上传路径
define('HOSTURL', 'http://' . $_SERVER['HTTP_HOST']); //定义网站家域名
define('PAGESIZE', 20); //默认每页显示条数
define('HTMLEDITOR', '../classes/kindeditor'); //定义富文本编辑器目录
define('UPLOADURL', '../../uploads'); //定义上传url路径
define('UPLOADPATH', WEBPATH . '/../uploads/'); //定义上传路径地址
//define('CACHEDB', ''); //数据库的缓存文件目录