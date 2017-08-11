<?php
namespace CLASSES;

use Swoole;

class AdminBase extends Swoole\Controller
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->session->start();

        /*不需要验证登录状态的控制器数组*/
        // $not_required_validate = array();
        // $not_required_validate = array('Login');
        // if (!in_array($this->swoole->env['mvc']['controller'], $not_required_validate)) {
        //     /*判断是否登录*/
        //     if (!isset($_SESSION['m_id']) || empty($_SESSION['m_id'])) {
        //         header('location:' . HOSTURL . '/Login/index');
        //         exit;
        //     }
        // }

        $this->public_assign();
    }
    protected function encrypt($password)
    {
        return md5($password);
    }
    public function public_assign()
    {
        if (defined("MANAGEURL")) {
            $this->tpl->assign("manageurl", MANAGEURL);
        }
        if (defined('HOSTURL')) {
            $this->tpl->assign("host_url", HOSTURL);
        }
    }
    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态(0成功，1失败)
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    protected function show_msg($message, $status = 1, $jumpUrl = '', $time = 3)
    {
        // if(true === $ajax) {// AJAX提交$ajax=false
        //     $data           =   is_array($ajax)?$ajax:array();
        //     $data['info']   =   $message;
        //     $data['status'] =   $status;
        //     $data['url']    =   $jumpUrl;
        //     $this->ajaxReturn($data);
        // }
        // if(is_int($ajax)) $this->assign('waitSecond',$ajax);
        if (!empty($jumpUrl)) {
            $this->assign('jumpUrl', $jumpUrl);
        }

        // 提示标题
        $this->tpl->assign('msgTitle', $status ? "操作成功" : "操作失败");

        $this->tpl->assign('status', $status); // 状态

        if ($status) {
            //发送成功信息
            $this->tpl->assign('message', $message); // 提示信息
            // 成功操作后默认停留3秒
            $this->tpl->assign('waitSecond', $time);
            // 默认操作成功自动返回操作前页面
            if (empty($jumpUrl)) {
                $this->tpl->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
            }
            $this->tpl->display("manager/show_msg.php");
            exit;
        } else {
            $this->tpl->assign('error', $message); // 提示信息
            //发生错误时候默认停留3秒
            $this->tpl->assign('waitSecond', $time);
            // 默认发生错误的话自动返回上页
            $this->tpl->assign('jumpUrl', "javascript:history.back(-1);");
            $this->tpl->display("manager/show_msg.php");
            // 中止执行  避免出错后继续执行
            exit;
        }
    }
}
