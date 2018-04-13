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
define('MANAGEPATH', WEBPATH . '../../manage');
define('FRAMEWORKPATH', WEBPATH . '/../../framework');
define('LIBRARIESPATH', WEBPATH . '/../../libraries');
define('APPPATH', WEBPATH . '/..');
define('STATICPATH','/views/static/');
define('STATICURL', 'http://static-app.gangjianwang.com');
define('MANAGEURL', STATICURL . '/manager/');//定义后台静态文件路径
// define('UPLOADURL', STATICURL . '/uploads/');//定义上传路径
define('HOSTURL', 'http://' . $_SERVER['HTTP_HOST']); //定义网站家域名
define('PAGESIZE', 25); //默认每页显示条数
define('HTMLEDITOR', '../classes/kindeditor'); //定义富文本编辑器目录
define('UPLOADURL', '/'); //定义上传url路径
define('UPLOADPATH', WEBPATH.'/../uploads'); //定义上传路径地址
define('PI',3.1415926535898);
define('EARTH_RADIUS',6378.137);
define('RECHARGE_CONFIRMATION',false);/*充值确认默认为false,true为开启人工确认充值,false为充值及时到账不需要验证*/
define('WXPAY_PATH', APPPATH."/../libraries/WxpayAPI");

// kindeditor文件上传路径必须在\o2o_web\apps\manage\classes\kindeditor\php\JSON.php下修改
//define('CACHEDB', ''); //数据库的缓存文件目录