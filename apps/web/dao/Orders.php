<?php
namespace WDAO;

class Orders extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Orders'));
    }

    //更改订单的支付状态
    public function payStatus($oid, $status = 0)
    {
        if (intval($oid) > 0 && -1 < intval($status))
        {
            $data = array(
                'o_pay' => intval($status),
                'o_pay_time' => time(),
            );
            return $this->updateData($data, array('o_id' => intval($oid)));
        }
        return false;
    }

}