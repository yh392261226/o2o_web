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
     * 重置任务状态到等待接受
     *  如果该任务有其他正常订单 则不变动
     */
    public function resetTaskToWait($t_id)
    {
        if (intval($t_id) > 0)
        {
            //获取该任务全部正常订单数
            $orders_dao = new \WDAO\Orders();
            $task_orders_count = $orders_dao->countData(array(
                't_id' => intval($t_id),
                'o_status' => 0,
            ));
            //如果订单数小于等于0  则将任务重置为待领取
            if ($task_orders_count <= 0)
            {
                return $this->updateData(array(
                    't_status' => 0,
                ), array(
                    't_id' => intval($t_id),
                ));
            }
        }
        return false;
    }
}