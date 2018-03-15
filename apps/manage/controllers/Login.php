<?php
namespace App\Controller;

use App;
/**
 * @action   登录,登出
 * @author   户连超
 * @addtime  2017.08.05
 * @e-mail    zrkjhlc@gmail.com
 */
class Login extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $this->tpl->display("Login/index.html");
    }

    public function doLogin()
    {
        if ($_POST['manager_name'] == '' && $_POST['manager_passwd'] == '') {
            $this->http->finish($this->json('', '-1', '用户名和密码不能为空!'));
        } elseif ($_POST['manager_name'] == '') {
            $this->http->finish($this->json('', '-2', '用户名不能为空!'));
        } elseif ($_POST['manager_passwd'] == '') {
            $this->http->finish($this->json('', '-3', '密码不能为空!'));
        } else {
            $manager_name = trim($_POST['manager_name']);
            $manager_passwd = trim($_POST['manager_passwd']);
            $login = new \DAO\Manager();
            $ret_info = $login->validateManager($manager_name, encyptPassword($manager_passwd));
            if (false === $ret_info) {
                $this->http->finish($this->json('', '-4', '用户名或密码错误!'));
            } else {
                $_SESSION['m_name'] = $ret_info['m_name'];
                $_SESSION['m_id'] = $ret_info['m_id'];
                $_SESSION['is_login'] = true;
                $this->http->finish($this->json('', '1', '登录成功!'));
            }
        }
    }

    public function logOut()
    {
        session_destroy();
        session_unset();
        /**
         * 发送Http状态码，如500, 404等等
         */
        $this->http->status(302);
        /**
         * 使用此函数代替PHP的header函数
         */
        $this->http->header('Location', HOSTURL);
    }
}
