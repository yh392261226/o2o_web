<?php
namespace MDAO;

class Users extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Users'));
    }

    /*处理微信支付结果添加记录和修改用户金额*/
    public function judgeReChargeRes($data)
    {
        /*平台资金流向记录*/
        if(isset($data['pfl_type_id']) && isset($data['pfl_amount']) && isset($data['u_id'])){
            $dao_funds_log = new \MDAO\Platform_funds_log();
            /*用户余额表*/
            $sql = 'update users_ext_funds set uef_overage = uef_overage + '. $data['pfl_amount'] .' where u_id = ' . $data['u_id'];
            $res_users_funds = $dao_funds_log ->queryData($sql);
            unset($data['u_id']);

            $data['pfl_type'] = 2;
            $data['pfl_reason'] = 'recharge';
            $data['pfl_in_time'] = time();
            $data['pfl_last_edit_time'] = time();
            $res_funds_log = $dao_funds_log -> addData($data);

            if($res_users_funds && $res_funds_log){
                return true;
            }
        }
        return false;

    }

    public function delUser($data = array())
    {
        $param = $this->createWhere($data);
        //print_r($param);exit;
        if (is_array($param) && !empty($param))
        {
            unset($param['page']);
            unset($param['pager']);
            unset($param['pagesize']);
        }
        if (empty($param))
        {
            $param = $data;
        }
        return model('Users')->delData2($param);
    }


}