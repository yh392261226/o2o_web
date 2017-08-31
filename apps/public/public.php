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
    return md5(md5($controllerName));
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
        Swoole::$php->tpl->display("show_msg.php");
        exit;
    } else {
        Swoole::$php->tpl->assign('error', $message); // 提示信息
        //发生错误时候默认停留3秒
        Swoole::$php->tpl->assign('waitSecond', $time);
        // 默认发生错误的话自动返回上页
        if (empty($jumpUrl)) {
            Swoole::$php->tpl->assign('jumpUrl', "javascript:history.back(-1);");
        }
        Swoole::$php->tpl->display("show_msg.php");
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
    $ip = '';
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
    if ('' == $ip) $ip = \Swoole::$php->request->getClientIP();
    if ($ip != '') {
        if ($type == 0) {
            return ip2long($ip);
        }
    }
    else
    {
        $ip = '0.0.0.0';
    }
    return $ip;
}

/**
 * 获取数组维度 或 验证数组是否是一维数组
 * $type 非0或false时 返回数组深度 为false时返回是否是一维数组
 */
function getArrayDeep($data = array(), $type = 0) {
    if (!is_array($data)) {
        return 0;
    } else {
        if ($type == 0) {
            if (count($data)==count($data, 1)) {
                return true; //一维数组
            } else {
                return false; //非一维数组
            }
        } else { //获取数组深度
            $max1 = 0;
            foreach ($data as $item1) {
                $t1 = getArrayDeep($item1);
                if ($t1 > $max1) {
                    $max1 = $t1;
                }

            }
            return $max1+1;
        }
    }
}


// function deepArrayFilter($data = array(), $index, $value){
//     if(is_array($array) && count($array)>0)
//     {
//         foreach(array_keys($array) as $key){
//             $temp[$key] = $array[$key][$index];

//             if ($temp[$key] == $value){
//                 $newarray[$key] = $array[$key];
//             }
//         }
//     }
//     return $newarray;
// }

function deepArrayFilter($data = array(), $filter = '')
{
    if (!empty($data))
    {
        //多维数组
        foreach ($data as $key => $val)
        {
            if (!is_array($val) && $val === $filter)
            {
                unset($data[$key]);
            }
            elseif (is_array($val))
            {
                $data[$key] = deepArrayFilter($val, $filter);
            }
        }
    }
    return $data;
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

//搜索数组中该值的键  只针对二维数组
function searchKey($value = '', $data = array())
{
    if ('' == trim($value) || empty($data))
    {
        return;
    }
    foreach ($data as $key => $val)
    {
        if (array_search($value, $val) !== false)
        {
            return $key;
        }
    }
    return;
}

function searchKeyDeep($value = '', $data =array())
{
    if ('' != trim($value) && !empty($data))
    {

    }
    return array();
}

/**
 * [logs description] 记录日志
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-08-16
 * @param  [string]            $path 路径
 * @param  [array || string]            $msg  要记录的信息
 */
function logs($path, $msg)
{
    if (!is_dir($path)) {
        mkdir($path);
    }
    $filename = $path . '/' . date('YmdHis') . '.txt';
    if (is_array($msg)) {
        $info = json_encode($msg);
    } else {
        $info = $msg;
    }
    $content = "------------------" . date("Y-m-d H:i:s") . "------------------" . "\r\n" . $info . "\r\n \r\n";
    file_put_contents($filename, $content, FILE_APPEND);
}

/**
 * 地区三级联动公共函数
 * 如果传$area_id返回当前id的地区名,如果不传返回p_id下的地区id和名称;
 * @author zhaoyu
 * @e-mail zhaoyu8292@qq.com
 * @date   2017-08-15
 * $parent 父id
 * $type 编号:  1是国家,2省份,3城市,
 * $target 列表框的id名称
 * @return [type]            [description]
 */
function area($parent=1,$type="1",$target="selProvinces",$area_id = "")
{
    if(!is_file("../../area.php")){
        return false;
    }
    $data = unserialize(file_get_contents("../../area.php"));
    if(!empty($area_id)){
        $area_name = "";
        foreach ($data as $key => $value) {
            if($value['r_id'] == $area_id){
                $area_name = $value['r_name'];
                return $area_name;
            }

        }
    }else{
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

}

/**
 * [checkUserPermissions description]检查用户权限
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-08-18
 * @return [type]            [description]
 * 未完待续......
 */
function checkUserPermissions()
{
    if (isset($_GET['s'])) {
        $permissions = explode("/", trim($_GET['s'],"/"));
        $str = implode("@", $permissions);
        encyptController($str);

    }
}
/**
 * 递归处理 输出所有子分类id
 * @author zhaoyu
 * @e-mail zhaoyu8292@qq.com
 * @date   2017-08-19
 * @param  [type]            $data    查找的数据
 * @param  [type]            $catid   id
 * @param  [type]            $key_id  子id字段名
 * @param  [type]            $key_pid 父id字段名
 * @param  boolean           $isClear 是否初始化静态变量
 * @return [type]            所有子分类id   例如:Array ( [0] => 22 [1] => 17 [2] => 23 [3] => 25 [4] => 24 )
 */
function getChildren($data,$catid,$key_id,$key_pid,$isClear=false)
{
    static $child = array();
    if($isClear){
        $child = array();
    }
    foreach ($data as $k => $v) {

        if($v[$key_pid] == $catid){
            $child[] = $v[$key_id];
            getChildren($data,$v[$key_id],$key_id,$key_pid);
        }
    }
    return $child;
}