<?php
/**
 * 订单接口
 */
namespace App\Controller;

class Orders extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->orders_dao = new \WDAO\Orders();
        //$this->db->debug = 1;
    }

    public function index()
    {
        $action = (isset($_REQUEST['action']) && '' != trim($_REQUEST['action'])) ? trim($_REQUEST['action']) : 'list';
        if ('' != trim($action))
        {
            $this->$action();
        }
    }

    /**
     * 订单详情
     */
    private function info()
    {
        $data = array();
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = intval($_REQUEST['o_id']); //订单id
        if (!empty($data) && isset($data['o_id']) && 0 < intval($data['o_id']))
        {
            $info = $this->orders_dao->infoData(intval($data['o_id']));
            if (!empty($info))
            {
                $this->exportData($info);
            }
        }
        $this->exportData();
    }

    /**
     * 工人确认订单/任务 并开始工作
     */
    private function confirm()
    {
        $data = array();
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = intval($_REQUEST['o_id']); //订单id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']); //任务id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']); //工人id

        if (!empty($data) && isset($data['o_id']) && isset($data['t_id']) && isset($data['o_worker']))
        {
            $info = $this->orders_dao->listData($data + array('pager' => 0, 'limit' => 1, 'order' => 'o_id desc'));

            if (isset($info['data'][0]) && !empty($info['data'][0]))
            {
                if (isset($info['data'][0]['o_confirm']) && intval($info['data'][0]['o_confirm']) > 0)
                {
                    $this->exportData('已经确认过了，无需再次确认');
                }

                //更新了订单状态
                $result = $this->orders_dao->updateData(array('o_confirm' => 1), $data);
                if (!$result)
                {
                    $this->exportData('failure');
                }

                //更新任务状态
                if (isset($info['data'][0]['t_id']) && 0 < intval($info['data'][0]['t_id']))
                {
                    $worker_dao = new \WDAO\Task_ext_worker();
                    $workers_result = $worker_dao->countData(array('t_id' => intval($info['data'][0]['t_id'])));
                    $orders_result  = $this->orders_dao->countData(array('t_id' => intval($info['data'][0]['t_id'])));
                    $task_dao = new \WDAO\Tasks();
                    if ($workers_result == $orders_result)
                    {//全开工
                        $task_dao->updateData(array('t_status' => 2), array('t_id' => intval($info['data'][0]['t_id'])));
                    }
                    else
                    {//半开工
                        $task_dao->updateData(array('t_status' => 5), array('t_id' => intval($info['data'][0]['t_id'])));
                    }
                }
                $this->exportData('success');
            }
        }
        $this->exportData('failure');
    }

    /**
     * 成单
     */
    private function create()
    {
        $data = array();
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = intval($_REQUEST['tew_id']); //任务工人关系id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']); //任务id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']); //工人id
        if (!isset($data['tew_id']) || !isset($_REQUEST['t_id']) || !isset($_REQUEST['o_worker']))
        {
            $this->exportData('failure');
        }

        $worker_dao = new \WDAO\Task_ext_worker();
        $worker_result = $worker_dao->listData(array(
            'pager' => 0,
            'fields' => 'orders.*, task_ext_worker.*',
            'where' => 'task_ext_worker.tew_id = ' . intval($data['tew_id']) . ' and task_ext_worker.t_id =' . intval($_REQUEST['t_id']),
            'leftjoin' => array('orders', 'orders.t_id = task_ext_worker.t_id'),
            'join' => array('tasks', 'tasks.t_id = task_ext_worker.t_id'),
            ));

        if (!empty($worker_result['data'][0]) && $worker_result['data'][0]['o_id'] <= 0 && $worker_result['data'][0]['t_id'])
        {
            $curtime = time();
            $result = $this->orders_dao->addData(array(
                't_id' => $worker_result['data'][0]['t_id'],
                'u_id' => $worker_result['data'][0]['t_author'],
                'o_worker' => $data['o_worker'],
                'o_amount' => $worker_result['data'][0]['tew_price'],
                'o_in_time' => $curtime,
                'o_last_edit_time' => $curtime,
                'tew_id' => $worker_result['data'][0]['tew_id'],
                's_id' => $worker_result['data'][0]['tew_skills'],
            ));
            if (!$result)
            {
                $this->exportData('failure');
            }
            $this->exportData($result);
        }
        $this->exportData('任务不存在或已被领取');
    }

    /**
     * 订单雇主改价
     */
    private function price()
    {
        $data = array();
        //1：根据条件 获取该条订单信息
        //2：改数据库中的数据
        //3：将取出来的信息 与 要改的信息做比对 得出差额
        //4：多退少补 操作平台与用户资金
    }

}