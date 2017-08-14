<?php
namespace App\Controller;

use App;

class Login extends \CLASSES\AdminBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        // $this->session->start();
    }
    public function index()
    {
        $this->tpl->display("Login/index.html");
    }
    public function doLogin()
    {
        if ($_POST['manager_name'] == '' && $_POST['manager_passwd'] == '') {
            $this->http->finish($this->json(array('err_code' => '-1', 'err_msg' => '用户名和密码不能为空!')));
        } elseif ($_POST['manager_name'] == '') {
            $this->http->finish($this->json(array('err_code' => '-2', 'err_msg' => '用户名不能为空!')));
        } elseif ($_POST['manager_passwd'] == '') {
            $this->http->finish($this->json(array('err_code' => '-3', 'err_msg' => '密码不能为空!')));
        } else {
            $manager_name   = trim($_POST['manager_name']);
            $manager_passwd = trim($_POST['manager_passwd']);
            $login          = new \DAO\Manager();
            $ret_info       = $login->validateManager($manager_name, encyptPassword($manager_passwd));
            if (false === $ret_info) {
                $this->http->finish($this->json(array('err_code' => '-4', 'err_msg' => '用户名或密码错误!')));
            } else {
                $_SESSION['m_name']   = $ret_info['m_name'];
                $_SESSION['m_id']     = $ret_info['m_id'];
                $_SESSION['is_login'] = true;
                $this->http->finish($this->json(array('err_code' => '1', 'err_msg' => '登录成功!')));
            }
        }
    }
    public function logOut()
    {
        session_destroy();
        session_unset();
        // header('Location:http://' . $_SERVER['HTTP_HOST']);
        //发送Http状态码，如500, 404等等
        $this->http->status(302);
        //使用此函数代替PHP的header函数
        $this->http->header('Location', HOSTURL);
    }
}
