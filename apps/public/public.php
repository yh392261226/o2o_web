<?php
/**
 * 公共函数库
 *
 */
/**
 * 模块值 加密方式
 * @author   户连超
 * @Email    zrkjhlc@gmail.com
 * @DateTime 2017-08-14
 * @param    string            $controllerName
 * @return string
 */
function encyptController($controllerName)
{
    return md5(md5($controllerName) . get_rand_str(6));
}

/*
 * 处理大小写
 */
function processData($data = array())
{
    if (!empty($data)) {
        return array_walk($data, 'strtolower', $data);
    } else {
        array_walk($_COOKIE, 'strtolower', $_COOKIE);
        array_walk($_SESSION, 'strtolower', $_SESSION);
    }
}

/**
 * 密码加密
 */
function encyptPassword($password = '')
{
    if ('' != $password) {
        return md5($password);
    }
    return '';
}

/*
 * 信息提示
 */
function msg($message, $status = 1, $jumpUrl = '', $time = 3)
{
    if (!empty($jumpUrl)) {
        Swoole::$php->tpl->assign('jumpUrl', $jumpUrl);
    }

    // 提示标题
    Swoole::$php->tpl->assign('msgTitle', $status ? "success" : "failure");

    Swoole::$php->tpl->assign('status', $status); // 状态

    if ($status) {
        //发送成功信息
        Swoole::$php->tpl->assign('message', $message); // 提示信息
        // 成功操作后默认停留3秒
        Swoole::$php->tpl->assign('waitSecond', $time);
        // 默认操作成功自动返回操作前页面
        if (empty($jumpUrl)) {
            Swoole::$php->tpl->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
        }
        Swoole::$php->tpl->display("manager/show_msg.php");
        exit;
    } else {
        Swoole::$php->tpl->assign('error', $message); // 提示信息
        //发生错误时候默认停留3秒
        Swoole::$php->tpl->assign('waitSecond', $time);
        // 默认发生错误的话自动返回上页
        Swoole::$php->tpl->assign('jumpUrl', "javascript:history.back(-1);");
        Swoole::$php->tpl->display("manager/show_msg.php");
        // 中止执行  避免出错后继续执行
        exit;
    }
}

function deepAddslashes($data = array())
{
    if (get_magic_quotes_gpc()) {
        return $data;
    }

    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $data[$key] = deepAddslashes($val);
        }
    } else {
        $data = addslashes($data);
    }

    return $data;
}

function deepStripslashes($data = array())
{
    if (get_magic_quotes_gpc()) {
        return $data;
    }

    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $data[$key] = deepStripslashes($val);
        }
    } else {
        $data = stripslashes($data);
    }

    return $data;
}

/*
 * 获取客户端ip地址
 */
function getIp($type = 0)
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($ip != '') {
        if ($type == 0) {
            return ip2long($ip);
        }
        return $ip;
    }
}

/*
 * 验证
 */
function validatas($data = array())
{

}

/*
 * 递归删除文件夹
 */
function delFile($dir, $file_type = '')
{
    if (is_dir($dir)) {
        $files = scandir($dir);
        //打开目录 //列出目录中的所有文件并去掉 . 和 ..
        foreach ($files as $filename) {
            if ($filename != '.' && $filename != '..') {
                if (!is_dir($dir . '/' . $filename)) {
                    if (empty($file_type)) {
                        unlink($dir . '/' . $filename);
                    } else {
                        if (is_array($file_type)) {
                            //正则匹配指定文件
                            if (preg_match($file_type[0], $filename)) {
                                unlink($dir . '/' . $filename);
                            }
                        } else {
                            //指定包含某些字符串的文件
                            if (false != stristr($filename, $file_type)) {
                                unlink($dir . '/' . $filename);
                            }
                        }
                    }
                } else {
                    delFile($dir . '/' . $filename);
                    rmdir($dir . '/' . $filename);
                }
            }
        }
    } else {
        if (file_exists($dir)) {
            unlink($dir);
        }

    }
}

/**
 * 获取随机字符串
 * @param int $randLength  长度
 * @param int $addtime  是否加入当前时间戳
 * @param int $includenumber   是否包含数字
 * @return string
 */
function get_rand_str($randLength = 6, $addtime = 1, $includenumber = 0)
{
    if ($includenumber) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    } else {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
    }
    $len = strlen($chars);
    $randStr = '';
    for ($i = 0; $i < $randLength; $i++) {
        $randStr .= $chars[rand(0, $len - 1)];
    }
    $tokenvalue = $randStr;
    if ($addtime) {
        $tokenvalue = $randStr . time();
    }
    return $tokenvalue;
}

/**
 * 随机一个订单号
 */
function createOrderNumber($prefix = '')
{
    $time = microtime();
    return encyptPassword($prefix . $time);
}
