<?php
namespace WDAO;

class Tasks extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Tasks'));
    }

    //删除任务及任务详情与任务工人
    public function delOldTask($data)
    {
        if (!empty($data))
        {
            $param = array();
            if (isset($data['t_id']) && intval($data['t_id']) > 0) $param['t_id'] = intval($data['t_id']);
            if (isset($data['t_author']) && intval($data['t_author']) > 0) $param['t_author'] = intval($data['t_author']);
            if (isset($data['t_storage']) && intval($data['t_storage']) > 0) $param['t_storage'] = intval($data['t_storage']);

            if (!empty($param) && (isset($param['t_id']) || isset($param['t_author'])))
            {
                if (!isset($param['t_id']))
                {
                    $param['pager'] = 0;
                    $list = $this->listData($param);
                    if (!empty($list['data']))
                    {
                        foreach ($list['data'] as $key => $val)
                        {
                            $param['t_id'][] = $val['t_id'];
                        }
                    }
                    unset($list);
                }

                $result = $this->delData(array('t_id' => array('value' => $param['t_id'], 'type' => 'in')));
                if (!$result)
                {
                    return false;
                }

                $where = $this->createWhere(array('t_id' => array('value' => $param['t_id'], 'type' => 'in'), 'pager' => 0));
                //删除任务详情
                $info_model = model('Task_ext_info');
                $info_model->delData($where);
                //删除任务相关工人信息
                $worker_model = model('Task_ext_worker');
                $worker_model->delData($where);
                return true;
            }
        }
        return false;
    }

    /**
     * 重置任务状态到等待接受或洽谈中
     *  如果该任务有正常工作中订单 则不变动
     */
    public function resetTaskToWait($t_id)
    {
        if (intval($t_id) > 0)
        {
            //获取该任务全部正常订单数
            $task_orders_count = array(
                'negotiate' => 0, //洽谈中
                'wait' => 0, //待联系
            );

            //全部所需工种
            $task_worker_dao = new \WDAO\Task_ext_worker();
            $workers_data = $task_worker_dao->listData(array(
                't_id' => intval($t_id),
                'tew_type' => 0,
                'tew_status' => 0,
                'pager' => 0,
            ));

            if (!empty($workers_data['data']))
            {
                //全部可用订单信息
                $orders_dao = new \WDAO\Orders();
                $orders_data = $orders_dao->listData(array(
                    't_id' => intval($t_id),
                    'pager' => 0,
                    'where' => 'o_status != -4',
                ));

                if (!empty($orders_data['data']))
                {
                    //说明有订单
                    foreach ($workers_data['data'] as $key => $val)
                    {
                        foreach ($orders_data['data'] as $k => $v)
                        {//订单与可用工种关联且订单在洽谈中状态
                            if ($val['tew_id'] == $v['tew_id'] && $v['o_status'] = 0 && in_array($v['o_confirm'], array(0, 2)))
                            {
                                $task_orders_count['negotiate'] += 1;
                            }
                        }
                    }
                }
                else
                {
                    //无订单 直接待联系
                    $task_orders_count['wait'] += 1;
                }
            }
            else
            {
                return false; //无可用工种
            }

            //如果有待联系或洽谈中 才修改
            if ($task_orders_count['wait'] > 0 || $task_orders_count['negotiate'] > 0)
            {
                $status = ($task_orders_count['negotiate'] > 0) ? 1 : 0;
                return $this->updateData(array(
                    't_status' => $status,
                ), array(
                    't_id' => intval($t_id),
                ));
            }
        }
        return false;
    }
}