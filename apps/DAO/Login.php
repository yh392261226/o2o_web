<?php
namespace DAO;

/**
 * 登录验证
 * @package App\DAO
 */
class Login
{
    /**
     * 用户登录检测用户名密码是否对应
     * $username 用户名
     * $password 密码
     */

    public function validateManager($username, $password)
    {
        if (!isset($username) || !isset($password) || empty(trim($username)) || empty(trim($username))) {
            return false;
        }
        $username = $this->strCheck(trim($username));
        $password = $this->strCheck(trim($password));
        $where    = array("m_name" => $username, "m_pass" => $password);
        $field    = 'm_id,m_name';
        $res      = model("Managers")->findOne($where, $field);
        if ($res['m_id'] > 0) {
            return $res;

        } else {

            return false;
        }
    }
    /**
     * sql语句过滤函数
     * @param  [type] $str [description] 要过滤的变量
     * @return [type]      [description] 过滤完的变量
     */
    private function strCheck($str)
    {
        // 判断magic_quotes_gpc是否打开
        if (!get_magic_quotes_gpc()) {
            $str = addslashes($str); // 进行过滤
        }
        $str = str_replace("_", "\_", $str); // 把 '_'过滤掉
        $str = str_replace("%", "\%", $str); // 把' % '过滤掉
        return $str;
    }
}
