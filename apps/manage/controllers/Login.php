<?php
namespace App\Controller;

use App;
use Swoole;

class Login extends Swoole\Controller
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->session->start();
    }
    public function index()
    {
        if (defined("MANAGEURL")) {
            $this->tpl->assign("manageurl", MANAGEURL);
        }
        $this->tpl->display("Login/index.html");
    }
    public function doLogin()
    {
        if ($_POST['manager_name'] == '' && $_POST['manager_passwd'] == '') {
            exit(json_encode(array('err_code' => '-1', 'err_msg' => '用户名和密码不能为空!')));
        } elseif ($_POST['manager_name'] == '') {
            exit(json_encode(array('err_code' => '-1', 'err_msg' => '用户名不能为空!')));
        } elseif ($_POST['manager_passwd'] == '') {
            exit(json_encode(array('err_code' => '-1', 'err_msg' => '密码不能为空!')));
        } else {
            $manager_name   = trim($_POST['manager_name']);
            $manager_passwd = trim($_POST['manager_passwd']);
            $login          = new \DAO\Login();
            $ret_info       = $login->validateManager($manager_name, $manager_passwd);
            if (false === $ret_info) {
                exit(json_encode(array('err_code' => '-1', 'err_msg' => '用户名或密码错误!')));
            } else {
                $_SESSION['m_name']   = $ret_info['m_name'];
                $_SESSION['m_id']     = $ret_info['m_id'];
                $_SESSION['is_login'] = true;
                exit(json_encode(array('err_code' => '1', 'err_msg' => '登录成功!')));
            }
        }
    }
    public function logOut()
    {
        session_destroy();
        session_unset();
        header('Location:http://' . $_SERVER['HTTP_HOST']);
    }
}
