<?php
/**
 * 公共函数库
 *
 */

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
    $len     = strlen($chars);
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

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci     = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? true : false;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2); /*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response    = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code   = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
}

/**
 * 地区三级联动公共函数
 * @author zhaoyu
 * @e-mail zhaoyu8292@qq.com
 * @date   2017-08-15
 * $parent 父id
 * $type 编号:  1是国家,2省份,3城市,
 * $target 列表框的id名称
 * @return [type]            [description]
 */
function area($parent=1,$type="1",$target="selProvinces")
{
    if(!is_file("../../area.php")){
        return false;
    }
    $data = unserialize(file_get_contents("../../area.php"));
    $res = array();
    $area_arr = array();
    foreach ($data as $key => $value) {
        if($value['r_pid'] == $parent){
            $area_arr[] = array('region_id'=>$value['r_id'],'region_name'=>$value['r_name']);

        }
    }
    $res['regions'] = $area_arr;
    $res['type'] = $type;
    $res['target'] = $target;
    return $res;
}
