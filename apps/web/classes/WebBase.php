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

        //echo $this->showTrace();
        //$this->db->debug = 1;
        $web_config = array();
        if (file_exists(WEBPATH . '/configs/web_config.php')){
            require WEBPATH . '/configs/web_config.php';
        }else{
            $dao_Web_config = new \WDAO\Users(array('table'=>'web_config'));
            $data = $dao_Web_config ->listData(array('pager'=>false,'fields'=>'wc_name,wc_value','wc_status'=>1,'web_id'=>1));

            $res = array();
            foreach ($data['data'] as  $v) {
                $res["{$v['wc_name']}"] = $v['wc_value'];
            }
            file_put_contents(WEBPATH . '/configs/web_config.php','<?php $web_config='.var_export($res,true).'?>');
            if (file_exists(WEBPATH . '/configs/web_config.php')){
                require WEBPATH . '/configs/web_config.php';
            }else{
                $this->exportData(0,array('msg'=>'系统错误请联系管理员'));
            }
        }
        $this->web_config = isset($web_config) ? $web_config : array();




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
    public function exportData($data = array(), $code = 200, $type = 'json')
    {
        $result = array();
        $result['code'] = $code;
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

    /**
     * 修改用户资金表数据
     * $type  类型
     * overage | ticket | envelope
     *      钱 |  代金券 | 红包
     */
    public function userFunds($uid, $amount, $type = 'envelope')
    {
        if (0 < intval($uid) && '' != trim($type))
        {
            switch ($type)
            {
                case 'overage':
                case 'pubtask':
                case 'withdraw':
                case 'recharge':
                    $sets = ' uef_overage = uef_overage + ' . $amount;
                    break;
                case 'ticket':
                    $sets = ' uef_ticket = uef_ticket + ' . $amount;
                break;
                case 'envelope':
                    $sets = ' uef_envelope = uef_envelope + ' . $amount;
                break;
            }
            $sql = 'update users_ext_funds set ' . $sets . ' where u_id = ' . $uid;
            //echo $sql;exit;
            $model = new \WDAO\Users_ext_funds(array('table' => 'users_ext_funds'));
            $result = $model->queryData($sql);
            if (!$result)
            {
                return false;
            }
            return true;
        }
    }

    /**
     * 充值日志记录
     * @param $uid
     * @param $amount
     * @param $name
     * @param $card
     * @param int $status
     * @param int $payid
     * @return bool
     */
    public function usersRechargeLog($uid, $amount, $name = '', $card = '', $status = 0, $payid = 0)
    {
        if (intval($uid) <= 0 || !is_float($amount) || !is_int($status) || intval($payid) < 0)
        {
            return false;
        }

        $recharge_dao = new \MDAOBASE\DaoBase(array('table' => 'user_recharge_log'));
        $log_data = array(
            'u_id' => $uid,
            'p_id' => $payid,
            'url_amount' => $amount,
            'url_truename' => $name,
            'url_card' => $card,
            'url_in_time' => time(),
            'url_status' => $status,
        );
        return $recharge_dao->addData($log_data);
    }

    /**
     * 用户提现日志记录
     * @param $uid
     * @param $amount
     * @param $name
     * @param $card
     * @param int $status
     * @param int $payid
     * @return bool
     */
    public function usersWithdrawLog($uid, $amount, $name = '', $card = '', $status = 0, $payid = 0)
    {
        if (intval($uid) <= 0 || !is_float($amount) || !is_int($status) || intval($payid) < 0)
        {
            return false;
        }
        $withdraw_dao = new \MDAOBASE\DaoBase(array('table' => 'user_withdraw_log'));
        $log_data = array(
            'u_id' => $uid,
            'p_id' => $payid,
            'uwl_amount' => $amount,
            'uwl_truename' => $name,
            'uwl_card' => $card,
            'uwl_in_time' => time(),
            'uwl_status' => $status,
        );
        return $withdraw_dao->addData($log_data);
    }

    /**
     * 平台资金流向日志
     * @param $type_id
     * @param $amount
     * @param int $type
     * @param string $reason
     * @param int $status
     * @return bool
     */
    public function platformFundsLog($type_id, $amount, $type = 0, $reason = '', $status = 0)
    {
        if (intval($type_id) < 1 || floatval($amount) <= 0)
        {
            return false;
        }
        $platform_funds_dao = new \WDAO\Platform_funds_log();
        $log_data = array(
            'pfl_type' => $type,
            'pfl_type_id' => $type_id,
            'pfl_amount' => $amount,
            'pfl_in_time' => time(),
            'pfl_reason' => $reason,
            'pfl_status' => $status,
        );
        return $platform_funds_dao->addData($log_data);
    }

    /*获取用户头像信息*/
    private function getHeadById($u_id = 0,$ext = '.jpg')
    {
        if(!is_dir($this ->web_config['u_img_path'])){
            $res = mkdir($this ->web_config['u_img_path'],0777,true);
            if(!$res){
                return '';
            }
        }
        if(file_exists($this ->web_config['u_img_path'].$u_id.$ext)){
            return $this ->web_config['u_img_url'].$u_id.$ext;
        }else{
            return $this ->web_config['u_img_url'].'0'.$ext;
        }
    }

}