<?php
namespace CLASSES;
use Swoole;
use Swoole\Controller;

class ManageBase extends Swoole\Controller
{
    static $manager_status = false;
    public $not_validata   = array('login');
    public $controller_name = '';
    public $view_name = '';
    public $template_ext = '.html';

    public function __construct($swoole)
    {
        parent::__construct($swoole);

        $this->session->start();
//         $this->validataLoginStatus(); //验证登陆状态
        $this->managerAssign();
        //$this->db->debug = true;

        if (!empty($this->swoole->env['mvc']))
        {
            $this->controller_name = $this->swoole->env['mvc']['controller'];
            $this->view_name = $this->swoole->env['mvc']['view'];
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
                if (!isset($_SESSION['manager_info']['m_id']) || empty($_SESSION['manager_info']['m_id']))
                {
                    self::$manager_status = 0;
                    header('Location:' . HOSTURL . '/Managers/login');
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

    public function managerAssign()
    {
        if (defined("MANAGEURL")) {
            $this->tpl->assign("manageurl", MANAGEURL);
        }
        if (defined('HOSTURL')) {
            $this->tpl->assign("host_url", HOSTURL);
        }
        $manager_info = !empty($_SESSION['manager']) ? $_SESSION['manager'] : array();
        $this->tpl->assign('manager_info', $manager_info);
        $this->tpl->assign('menu_list', $this->config['menu']);
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
            $template = ucfirst($this->controller_name) . '/' . $this->view_name . $this->template_ext;
        }
        $this->tpl->display($template);
    }

}