<?php
namespace WDAO;

class Orders extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Orders'));
    }

    //更改订单的支付状态
    public function payStatus($oid, $status = 0, $o_status = 1)
    {
        if (intval($oid) > 0 && -1 < intval($status))
        {
            $data = array(
                'o_pay' => intval($status),
                'o_pay_time' => time(),
                //'o_status' => 1,
            );
            if ($o_status >= 0)
            {
                $data['o_status'] = 1;
            }
            return $this->updateData($data, array('o_id' => intval($oid)));
        }
        return false;
    }

    /**
     *  删除多个订单 即更改多个订单的状态
     */
    public function delOrders($data = array())
    {
        if (!empty($data))
        {
            $param = $data;
            $param['pager'] = 0;
            $orders_data = $this->listData($param);
            if (!empty($orders_data['data']))
            {
                $tmp_ids = array();
                foreach ($orders_data['data'] as $key => $val)
                {
                    if (isset($val['o_id']) && $val['o_id'] > 0)
                    {
                        $tmp_ids[] = $val['o_id'];
                    }
                }

                if (!empty($tmp_ids))
                {
                    //return $this->realDel(array('where' => 'o_id in (' . implode(',', $tmp_ids) . ')'));
                    $update_param = $this->createWhere(array('o_id' => array('type' => 'in', 'value' => $tmp_ids), 'pager' => 0));
                    return $this->updateData(array('o_status' => -4), $update_param);
                }
            }
        }
        return false;
    }
    //修改订单状态为完成
    public function editOrders($data=array())
    {

        if (!empty($data))
        {
            $param = $data;
            $param['pager'] = 0;
            $orders_data = $this->listData($param);
            if (!empty($orders_data['data']))
            {
                $tmp_ids = array();
                foreach ($orders_data['data'] as $key => $val)
                {
                    if (isset($val['o_id']) && $val['o_id'] > 0)
                    {
                        $tmp_ids[] = $val['o_id'];
                    }
                }

                if (!empty($tmp_ids))
                {
                    $update_param = $this->createWhere(array('o_id' => array('type' => 'in', 'value' => $tmp_ids), 'pager' => 0));
                    return $this->updateData(array('o_status' => 1), $update_param);
                }
            }
        }
        return false;
    }



}