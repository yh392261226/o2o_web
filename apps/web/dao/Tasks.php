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
     * 利用任务剩余工种的状态 重置任务状态
     */
    public function resetTaskByLastWork($t_id)
    {
        if (intval($t_id) > 0)
        {
            $task_status = -1;
            //获取该任务的所有未完成工种信息
            $task_worker_dao = new \WDAO\Task_ext_worker();
            $workers_data = $task_worker_dao->listData(array( //未完结的都取
                't_id' => intval($t_id),
                'tew_status' => 0,
                'pager' => 0,
            ));
            if (!empty($workers_data['data']))
            {
                $tmp_tew_ids = $tmp = array();
                foreach ($workers_data['data'] as $key => $val)
                {
                    if (isset($val['tew_id']) && $val['tew_id'] > 0)
                    {
                        $tmp_tew_ids[] = $val['tew_id'];
                        $tmp[$val['tew_id']] = $val;
                    }
                }
                $workers_data = $tmp; //转化下所需工种信息
                unset($key, $val);

                if (!empty($tmp_tew_ids))
                {
                    //全部订单
                    $orders_dao = new \WDAO\Orders();
                    $orders_data = $orders_dao->listData(array(
                    't_id' => intval($t_id),
                    'pager' => 0,
                    'where' => 'o_status != -4 and tew_id in (' . implode(',', $tmp_tew_ids) . ')',
                    ));
                    if (!empty($orders_data['data']))
                    {
                        //有订单信息
                        foreach ($orders_data['data'] as $key => $val)
                        {//所有订单中的工种id
                            $tmp_order_tew_ids[] = $val['tew_id'];
                        }
                        unset($key, $val);

                        foreach ($orders_data['data'] as $key => $val)
                        {
                            if (($val['o_status'] == 0 && $val['o_confirm'] == 1 && $val['o_pay'] == 0) || ($val['o_status'] == -3 && $val['o_confirm'] == 1 && $val['o_pay'] == 0))
                            { //纠纷中 或工作中 都算工作中
                                $task_status = 2;
                                break;
                            }
                            elseif ($val['o_status'] == 0 && in_array($val['o_confirm'], array(0, 2))) //洽谈中
                            {
                                $task_status = 1;
                                break;
                            }
                        }
                        unset($key, $val);

                        //工作中 且有未开工的单 即为半开工状态
                        sort($tmp_order_tew_ids);
                        sort($tmp_tew_ids);
                        if (!empty($tmp_order_tew_ids) && $tmp_order_tew_ids != $tmp_tew_ids)
                        {
                            //有待联系的单
                            if ($task_status == 2)
                            {
                                $task_status = 5;
                            }
                        }

                    }
                    else
                    {
                        //待联系
                        $task_status = 0;
                    }
                }
            }
            else
            {
                //都完成了 即任务已经完结
                $task_status = 3;
            }

            if ($task_status != -1)
            {
                return $this->updateData(array(
                    't_status' => $task_status,
                ), array(
                    't_id' => intval($t_id),
                ));
            }
        }
        return false;
    }
}