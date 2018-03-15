<?php
namespace WDAO;

class Task_ext_worker extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Task_ext_worker'));
    }


    /**
     * 获取所有完成日期为昨天的任务信息及全部可用订单
     */
    public function getTaskWorkersWithOrders($param = array())
    {
        if (!empty($param)) {
            $data = $this->listData($param);
            if (!empty($data['data']))
            {
                $tmp = $tmp['tew_id'] = $tmp['t_id'] = array();
                foreach ($data['data'] as $key => $val)
                {
                    if (isset($val['tew_id']) && intval($val['tew_id']) > 0) $tmp['tew_id'][] = $val['tew_id'];
                    if (isset($val['t_id']) && intval($val['t_id']) > 0) $tmp['t_id'][] = $val['t_id'];
                    $data['data'][$key]['orders'] = array();
                }
                unset($key, $val);

                if (!empty($tmp['t_id']) && !empty($tmp['tew_id']))
                {
                    $orders_param = array();
                    $orders_param['pager'] = 0;
                    $orders_param['o_confirm'] = 1;
                    $orders_param['o_status'] = array('type' => 'in', 'value' => '-2, -1, 0, 1');
                    $orders_param['where'] = 't_id in (' . implode(',', $tmp['t_id']) . ') and tew_id in (' . implode(',', $tmp['tew_id']) . ')';
                    $orders_dao = new \WDAO\Orders();
                    $orders_data = $orders_dao->listData($orders_param);
                    if (!empty($orders_data['data']))
                    {
                        foreach ($data['data'] as $key => $val)
                        {
                            foreach ($orders_data['data'] as $k => $v)
                            {
                                if (isset($v['tew_id']) && $v['tew_id'] == $val['tew_id'])
                                {
                                    $data['data'][$key]['orders'][] = $v;
                                }
                            }
                            unset($k, $v);
                        }
                        unset($key, $val);
                    }
                    unset($orders_dao, $orders_param, $orders_data);
                }

                return $data['data'];
            }
        }
        return array();
    }

}