<?php
namespace CLASSES;

use Swoole;

class AdminBase extends Swoole\Controller
{
    /**
     * 构造函数
     * 开启session
     * 判断登录状态
     * 公共模板赋值
     */
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->session->start();

        /*不需要验证登录状态的控制器数组*/
        $not_required_validate = array('login');
        $controller = strtolower($this->swoole->env['mvc']['controller']);
        if (!in_array($controller , $not_required_validate)) {
            /*判断是否登录*/
            if (!isset($_SESSION['m_id']) || empty($_SESSION['m_id'])) {
                header('location:' . HOSTURL . '/Login/index');
                exit;
            }
        }
        if (isset($_GET['debug']) && $_GET['debug'] > 0) {
            echo $this->showTrace(true);
        }
        if (isset($_GET['showSql']) && $_GET['showSql'] > 0) {
            $this->db->debug = true;
        }
        $this->public_assign();
    }
    /**
     * 前台模板公共赋值
     */
    public function public_assign()
    {
        if (defined("MANAGEURL")) {
            $this->tpl->assign("manageurl", MANAGEURL);
        }
        if (defined('HOSTURL')) {
            $this->tpl->assign("host_url", HOSTURL);
        }
    }
}
