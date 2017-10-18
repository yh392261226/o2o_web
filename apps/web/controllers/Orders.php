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
     * 雇主确认订单/任务
     */
    private function employerConfirm()
    {
        $data = array();
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = intval($_REQUEST['o_id']); //订单id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']); //任务id
        if (isset($_REQUEST['u_id']) && intval($_REQUEST['u_id']) > 0) $data['u_id'] = intval($_REQUEST['u_id']); //雇主id
        if (!empty($data) && isset($data['o_id']) && isset($data['t_id']) && isset($data['u_id']))
        {
            $info = $this->orders_dao->listData($data + array('pager' => 0, 'limit' => 1, 'order' => 'o_id desc'));
            if (isset($info['data']) && !empty($info['data'][0]))
            {
                if (isset($info['data'][0]['o_confirm']) && $info['data'][0]['o_confirm'] == 2) $this->exportData('已经确认过了，无需再次确认');
                //更新了订单状态
                $result = $this->orders_dao->updateData(array('o_confirm' => 2), $data);
                if (!$result)
                {
                    $this->exportData('failure');
                }
            }
        }
        $this->exportData('failure');
    }

    /**
     * 工人确认订单/任务 并开始工作
     */
    private function workerConfirm()
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
                if (isset($info['data'][0]['o_confirm']) && intval($info['data'][0]['o_confirm']) == 1)
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
                    $orders_result  = $this->orders_dao->countData(array('t_id' => intval($info['data'][0]['t_id'], 'o_confirm' => 1)));
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
        //任务工人关系id
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = intval($_REQUEST['tew_id']);
        //任务id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']);
        //工人id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']);

        if (!isset($data['tew_id']) || !isset($_REQUEST['t_id']) || !isset($_REQUEST['o_worker'])) $this->exportData('failure');

        $worker_dao = new \WDAO\Task_ext_worker();
        $worker_result = $worker_dao->listData(array(
            'pager' => 0,
            'fields' => 'orders.*, task_ext_worker.*',
            'where' => 'task_ext_worker.tew_id = ' . intval($data['tew_id']) . ' and task_ext_worker.t_id =' . intval($_REQUEST['t_id'])  . ' and orders.o_confirm != 1',
            'leftjoin' => array('orders', 'orders.t_id = task_ext_worker.t_id'),
            'join' => array('tasks', 'tasks.t_id = task_ext_worker.t_id'),
            'order' => 'orders.o_id desc',
            ));

        if (empty($worker_result['data']) || !isset($worker_result['data'][0]['tew_worker_num']) || $worker_result['data'][0]['tew_worker_num'] < 1 ||
            !isset($worker_result['data'][0]['t_id']) || $worker_result['data'][0]['t_id'] <= 0 ||
            !isset($worker_result['data'][0]['t_author']) || $worker_result['data'][0]['t_author'] <= 0 ||
            !isset($worker_result['data'][0]['tew_price']) || $worker_result['data'][0]['tew_price'] <= 0 ||
            !isset($worker_result['data'][0]['tew_id']) || $worker_result['data'][0]['tew_id'] <= 0 ||
            !isset($worker_result['data'][0]['tew_skills']) || $worker_result['data'][0]['tew_skills'] <= 0)
        {
            $this->exportData('数据异常,请稍后重试');
        }

        //已成单数量判断
        if (count($worker_result['data']) > 0 && $worker_result['data'][0]['tew_worker_num'] <= count($worker_result['data'])) $this->exportData('你来晚了，任务已被领取');

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

    /**
     * 订单雇主改价
     */
    private function price()
    {
        $this->db->debug = 1;
        $data = $tmp = $task_param = array();
        //任务工人关系id
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = $task_param['tew_id'] = intval($_REQUEST['tew_id']);
        //任务id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $tmp['t_id'] = intval($_REQUEST['t_id']);
        //雇主id
        if (isset($_REQUEST['t_author']) && intval($_REQUEST['t_author']) > 0) $data['t_author'] = intval($_REQUEST['t_author']);
        //传递价格
        if (isset($_REQUEST['amount']) && intval($_REQUEST['amount']) > 0) $data['tew_price'] = floatval($_REQUEST['amount']);
        if (isset($_REQUEST['worker_num']) && intval($_REQUEST['worker_num']) > 0) $data['worker_num'] = intval($_REQUEST['worker_num']);
        if (isset($_REQUEST['start_time']) && intval($_REQUEST['start_time']) > 0) $data['start_time'] = strtotime($_REQUEST['start_time']);
        if (isset($_REQUEST['end_time']) && intval($_REQUEST['end_time']) > 0) $data['end_time'] = strtotime($_REQUEST['end_time']);
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']);

        if (!empty($data) && isset($data['tew_id']) && $data['tew_id'] > 0 &&
            isset($tmp['t_id']) && $tmp['t_id'] > 0 &&
            isset($data['t_author']) && $data['t_author'] > 0 &&
            isset($data['amount']) && $data['amount'] > 0 &&
            isset($data['worker_num']) && $data['worker_num'] > 0 &&
            isset($data['o_worker']) && $data['o_worker'] > 0)
        {
            //1：根据条件 获取该条订单信息
            $task_dao = new \WDAO\Task_ext_worker();
            $task_param['join'] = array('tasks', 'tasks.t_id = task_ext_worker.t_id');
            $task_param['where'] = ' task_ext_worker.t_id = "' . $tmp['t_id'] . '"';
            $task_param['pager'] = 0;
            $task_param['fields'] = 'tasks.*, task_ext_worker.*';
            $task_data = $task_dao->listData($task_param);
            if (!empty($task_data['data'][0]))
            {
                $task_data = $task_data['data'][0];
                $orders_data = $this->orders_dao->listData(array(
                    'tew_id' => $data['tew_id'],
                    't_id' => $tmp['t_id'],
                    'u_id' => $data['t_author'],
                    's_id' => $task_data['tew_skills'],
                    'pager' => 0,
                ));

                if (!empty($orders_data['data'][0]))
                {
                    $this->exportData('数据异常');
                }
                //2：将取出来的信息 与 要改的信息做比对 得出差额
                //改后价
                $tmp['edit_amount'] = ($data['tew_price'] * $data['worker_num'] * ceil(($data['end_time'] - $data['start_time']) / 3600 / 24));
                //改价前后差价 = 原价 - 改后价
                $tmp['agio'] = $orders_data['data'][0]['tew_price'] - $tmp['edit_amount'];
                if ($tmp['agio'] < 0)
                {
                    $user_funds_dao = new \WDAO\Users_ext_funds(array('table' => 'Users_ext_funds'));
                    $user_funds_data = $user_funds_dao->infoData($data['t_author']);
                    if (empty($user_funds_data) || (isset($user_funds_data['overage']) && $user_funds_data['overage'] < (-1 * $tmp['agio'])))
                    {
                        $this->exportData('余额不足，请充值');
                    }
                }


                $this->db->start();
                //3：改数据库中的数据
                //任务更新改价次数
                $times_update = $task_dao->queryData('update tasks set t_amount_edit_times=t_amount_edit_times+1, t_amount=t_amount + ' . ($tmp['agio'] * -1) . ', t_last_edit_time = ' . time() . ', t_last_editor = ' . $data['t_author'] . ' where t_id = "' . $tmp['t_id'] . '"');

                $orders_update = true;
                if (!empty($orders_data['data'][0]))
                {
                    //更改该工人的订单价格
                    $orders_update = $this->orders_dao->updateData(array(
                        'o_amount' => $data['tew_price'],
                        'o_confirm' => 0,
                        'o_last_edit_time' => time()), array(
                            't_id' => $tmp['t_id'],
                            'tew_id' => $data['tew_id'],
                            'o_worker' => $data['o_worker'],
                            's_id' => $task_data['tew_skills']));
                }

                //4：多退少补 操作平台与用户资金
                $user_funds_result = $this->userFunds($data['t_author'], $tmp['total_edit'], $type = 'changeprice'); //用户资金
                $platform_funds_result = $this->platformFundsLog($tmp['t_id'], (-1 * $tmp['total_edit']), 3, 'changeprice', 0);     //平台资金日志

                if ($times_update && $orders_update && $user_funds_result && $platform_funds_result)
                {
                    $this->db->commit();
                    $this->exportData('success');
                }
                else
                {
                    $this->db->rollback();
                }
            }
        }
        $this->exportData('failure');
    }

    /**
     * 订单支付
     */
    private function payout()
    {

    }

}