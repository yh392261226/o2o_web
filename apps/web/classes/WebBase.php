<?php
namespace CLASSES;
use Swoole;
use Swoole\Controller;

class WebBase extends Swoole\Controller
{
    static $user_status = 0;
    public $controller_name = '';
    public $view_name = '';

    public function __construct($swoole)
    {
        parent::__construct($swoole);
        //$this->db->debug = 1;
        $app_config = array();
        if (file_exists(WEBPATH . '/configs/application_config.php')) require WEBPATH . '/configs/application_config.php';
        $this->app_config = $app_config;


        $this->session->start();

        if (!empty($this->swoole->env['mvc']))
        {
            $this->controller_name = $this->swoole->env['mvc']['controller'];
            $this->view_name = $this->swoole->env['mvc']['view'];
        }
        //$this->validataTokenStatus(); //验证token值是否有效;

    }

    /**
     * @验证是否需要验证登陆状态
     * @return boolean
     */
    protected function validataTokenStatus()
    {
        /*获取设置的token有效期*/
        $token_valid_time = $this->app_config['token_valid']*24*3600;
        self::$manager_status = 0;
        if ('' != $this->controller_name && '' != $this->view_name)
        {
            if (!in_array($this->controller_name . '_' . $this->view_name, $this->not_validata))
            {
                //需要验证状态
                if (isset($_SESSION['manager']['m_id']) && $_SESSION['manager']['m_id'] > 0)
                {
                    self::$manager_status = $_SESSION['manager']['m_id'];
                }
                else
                {
                    echo '<script>window.location.href="/Managers/login"</script>';exit;
                }

            }
            return false;
        }
        echo '<script>window.location.href="/Managers/login"</script>';exit;
    }

    /*
     * 前台接口输出
     */
    public function exportData($data = array(), $type = 'json')
    {
        $result = array();
        $result['code'] = 200;
        $result['data'] = array();

        if (!empty($data))
        {
            $result['data'] = $data;
        }

        switch($type)
        {
            case 'json':
                echo json_encode($result);exit;
                break;
            default:
                echo json_encode($result);exit;
                break;
        }
    }


}