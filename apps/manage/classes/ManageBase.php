<?php
namespace CLASSES;
use Swoole;
use Swoole\Controller;

class ManageBase extends Swoole\Controller
{
    static $manager_status = false;
    public $not_validata   = array('login');
    public $controller_name = '';
    public $action_name = '';
    public $template_ext = '.html';

    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->session->start();
//         $this->validataLoginStatus(); //验证登陆状态
        $this->publicAssign();
        $this->db->debug = true;
        $control_action = isset($_GET['s']) ? trim($_GET['s']) : '';
        if ($control_action != '')
        {
            $tmparray = array();
            $tmparray = explode('/', $control_action);
            //print_r($tmparray);
            if (!empty($tmparray))
            {
                $this->controller_name = isset($tmparray[1]) ? $tmparray[1] : 'index';
                $this->action_name = isset($tmparray[2]) ? $tmparray[2] : 'index';
            }
        }

    }

    /**
     * @验证是否需要验证登陆状态
     * @return boolean
     */
    protected function validataLoginStatus()
    {
        $controller_name = strtolower($this->swoole->env['mvc']['controller']);
        if ('' != $controller_name)
        {
            if (!in_array($controller_name, $this->not_validata))
            {
                //需要验证状态
                if (!isset($_SESSION['m_id']) || empty($_SESSION['m_id']))
                {
                    self::$manager_status = 0;
                    header('Location:' . HOSTURL . '/index/login');
                    exit;
                }
                self::$manager_status = 1;
                return true;
            }
            return false; //不需要验证
        }
        return false;
    }

    /**
     * @模板公共赋值
     */
    protected function publicAssign()
    {
        if (defined("MANAGEURL")) {
            $this->tpl->assign("manageurl", MANAGEURL);
        }
        if (defined('HOSTURL')) {
            $this->tpl->assign("host_url", HOSTURL);
        }
    }

    /**
     * 合并基本条件
     * @param array $data
     */
    protected function params($data)
    {
        $param = array();
        if (!empty($data))
        {
            if (is_array($data))
            {

            }
        }
        else
        {
            $param = $data;
        }
        return $param;
    }

    public function mydisplay($name = '')
    {
        if ('' != trim($name))
        {
            $template = $name . $this->template_ext;
        }
        else
        {
            $template = ucfirst($this->controller_name) . '/' . $this->action_name . $this->template_ext;
        }
        $this->tpl->display($template);
    }

}