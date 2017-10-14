<?php
namespace WDAO;

class Platform_funds_log extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Platform_funds_log'));
    }

    /**
     * 平台资金返还给用户
     * @param array $data
     * @return bool
     */
    public function rebackFundsToUser($data = array())
    {
        if (!empty($data))
        {
            $param = array();
            if (isset($data['pfl_type']) && intval($data['pfl_type']) > 0) $param['pfl_type'] = intval($data['pfl_type']);
            if (isset($data['pfl_type_id']) && intval($data['pfl_type_id']) > 0) $param['pfl_type_id'] = intval($data['pfl_type_id']);
            if (isset($data['pfl_status'])) $param['pfl_status'] = intval($data['pfl_status']);
            if (isset($data['pfl_reason']) && '' != trim($data['pfl_reason'])) $param['pfl_reason'] = trim($data['pfl_reason']);
            if (isset($data['u_id']) && intval($data['u_id']) > 0) $u_id = intval($data['u_id']);
            if (!isset($u_id))
            {
                return false;
            }

            if (!empty($param))
            {
                $param['order'] = ' pfl_in_time desc ';
                $param['limit'] = ' 1 ';
                $param['pager'] = 0;
                $info = $this->listData($param);
                //print_r($info);exit;
                if (!empty($info['data'][0]) && isset($info['data'][0]['pfl_amount']) && $info['data'][0]['pfl_amount'] > 0)
                {
                    \Swoole::$php->db->start();
                    //更新给用户的资金
                    $sql = 'update users_ext_funds set uef_overage = uef_overage+'.$info['data'][0]['pfl_amount'].' where u_id = ' . $u_id;
                    if ('' != $sql)
                    {
                        //加给用户
                        $user_funds_model = model('Users_ext_funds');
                        $user_funds_result = $user_funds_model->queryData($sql);
                    }
                    //减去平台资金表
                    $platform_funds_model = model('Platform_funds_log');
                    $platform_funds_result = $platform_funds_model->addData(array(
                        'pfl_type' => $info['data'][0]['pfl_type'],
                        'pfl_type_id' => $info['data'][0]['pfl_type_id'],
                        'pfl_amount' => ($info['data'][0]['pfl_amount'] * -1),
                        'pfl_in_time' => time(),
                        'pfl_reason' => 'backtouser',
                        'pfl_status' => $info['data'][0]['pfl_status'],

                    ));
                    if ($user_funds_result && $platform_funds_result)
                    {
                        \Swoole::$php->db->commit();
                        return true;
                    }
                    else
                    {
                        \Swoole::$php->db->rollback();
                    }

                }
            }
            else
            {//没有 自然返回成功
                return 2;
            }
        }
        return false;
    }

}