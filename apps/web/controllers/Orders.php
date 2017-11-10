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
        $action = (isset($_REQUEST['action']) && '' != trim($_REQUEST['action'])) ? trim($_REQUEST['action']) : 'info';
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
                $this->msgToUser(array(
                    'author' => 0,
                    'type'   => 1,
                    'status' => 1,
                    'to_uid' => $info['data'][0]['o_worker'],
                    'title'  => '【任务：雇主确认】',
                    'desc'   => '雇主已确认任务，请进入工人工作管理中查看',
                ));
                $this->exportData('success');
            }
        }
        $this->exportData('failure');
    }

    /**
     * 工人确认订单/任务 并开始工作
     */
    private function workerConfirm()
    {
        //$this->db->debug = 1;
        $data = array();
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = intval($_REQUEST['o_id']); //订单id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']); //任务id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']); //工人id

        if (!empty($data) && isset($data['o_id']) && isset($data['t_id']) && isset($data['o_worker']))
        {
            $orders_param = $data;
            $orders_param['pager'] = 0;
            $orders_param['limit'] = 1;
            $orders_param['where'] = 'o_status = 0';
            $orders_param['order'] = 'o_id desc';
            $info = $this->orders_dao->listData($orders_param);
            //print_r($info);

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
                    $begin_status = 'all'; //默认开工状态为全开工
                    $order_counts = $worker_counts = 0; //数量初始值
                    $orders_skills_counts = array(); //工种数量数组
                    //全部订单数量 (含有已开工或未开工)
                    $orders_result  = $this->orders_dao->listData(array(
                        't_id' => intval($info['data'][0]['t_id']),
                        'o_confirm' => 1,
                        'pager' => 0,
                        'o_status' => array('type' => 'in', 'value' => array(0, -1, -2, -3, 1, 2))));
                    if (!empty($orders_result['data']))
                    {
                        foreach ($orders_result['data'] as $key => $val)
                        {
                            if (isset($val['s_id']) && $val['s_id'] > 0)
                            {
                                $order_counts += 1;
                                $orders_skills_counts[$val['s_id']] += 1;
                            }
                        }
                        unset($key, $val);
                    }
                    //工人总数
                    $workers_skills_counts = array();
                    $worker_dao = new \WDAO\Task_ext_worker();
                    $workers_result = $worker_dao->listData(array('t_id' => intval($info['data'][0]['t_id']))); //工种数据
                    if (!empty($workers_result['data']))
                    {
                        foreach ($workers_result['data'] as $key => $val)
                        {
                            if (isset($val['tew_skills']) && $val['tew_skills'] > 0 && isset($val['tew_worker_num']) && $val['tew_worker_num'] > 0)
                            {
                                $workers_skills_counts[$val['tew_skills']] += $val['tew_worker_num'];
                                $worker_counts += $val['tew_worker_num'];
                            }
                        }
                    }
                    //当前工种所需工人总数, 当前工种订单总数
                    if ($orders_skills_counts[$info['data'][0]['s_id']] >= $workers_skills_counts[$info['data'][0]['s_id']])
                    {
                        //任务所需工人技能块开工
                        $worker_dao->updateData(array('tew_type' => 1), array('tew_id' => $info['data'][0]['tew_id']));
                    }

                    if ($worker_counts > $order_counts) //所需工人总数大于订单工人总数 = 半开工
                    {
                        $begin_status = 'half';
                    }

                    //任务开工状态修改
                    $task_dao = new \WDAO\Tasks();
                    if ($begin_status == 'all')
                    {//全开工
                        $task_dao->updateData(array('t_status' => 2), array('t_id' => intval($info['data'][0]['t_id'])));
                    }
                    else
                    {//半开工
                        $task_dao->updateData(array('t_status' => 5), array('t_id' => intval($info['data'][0]['t_id'])));
                    }
                    //变更工人状态为忙
                    $user_dao = new \WDAO\Users(array('table' => 'users'));
                    $user_dao->taskStatus($data['o_worker'], '1');
                    //发送站内信
                    $this->msgToUser(array(
                        'author' => 0,
                        'type'   => 1,
                        'status' => 1,
                        'to_uid' => $info['data'][0]['u_id'],
                        'title'  => '【任务：工人确认】',
                        'desc'   => '工人已确认任务，请进入雇主发布任务管理中查看',
                    ));
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
        //$this->db->debug = 1;
        $data = array();
        //任务工人关系id
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = intval($_REQUEST['tew_id']);
        //任务id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']);
        //工人id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']);
        //发起人id
        if (isset($_REQUEST['o_sponsor']) && intval($_REQUEST['o_sponsor']) > 0) $data['o_sponsor'] = intval($_REQUEST['o_sponsor']);
//print_r($_REQUEST);exit;
        if (!isset($data['tew_id']) || !isset($data['t_id']) || !isset($data['o_worker']) || !isset($data['o_sponsor'])) $this->exportData('failure');


        //验证任务是否存在
        $worker_dao = new \WDAO\Task_ext_worker();
        $worker_result = $worker_dao->listData(array(
            'pager' => 0,
            'fields' => 'tasks.*, task_ext_worker.*',
            'where' => 'task_ext_worker.tew_id = ' . intval($data['tew_id']) . ' and task_ext_worker.t_id =' . intval($_REQUEST['t_id']),
            'join' => array('tasks', 'tasks.t_id = task_ext_worker.t_id'),
            'order' => 'task_ext_worker.tew_id desc',
            ));
        $worker_result = isset($worker_result['data'][0]) ? $worker_result['data'][0] : array();

        if (empty($worker_result) || !isset($worker_result['tew_worker_num']) || $worker_result['tew_worker_num'] < 1 ||
            !isset($worker_result['t_id']) || $worker_result['t_id'] <= 0 ||
            !isset($worker_result['t_author']) || $worker_result['t_author'] <= 0 ||
            !isset($worker_result['tew_price']) || $worker_result['tew_price'] <= 0 ||
            !isset($worker_result['tew_id']) || $worker_result['tew_id'] <= 0 ||
            !isset($worker_result['tew_skills']) || $worker_result['tew_skills'] <= 0)
        {
            $this->exportData('数据异常,请稍后重试');
        }

        //传参是否已经存储成功
        $orders_count = $this->orders_dao->countData(array(
            't_id' => $worker_result['t_id'],
            'u_id' => $worker_result['t_author'],
            'o_worker' => $data['o_worker'],
            'tew_id' => $worker_result['tew_id'],
            's_id' => $worker_result['tew_skills'],
            'where' => 'o_status != -4'));
        if ($orders_count > 0) $this->exportData('已经成功邀约，无需再次邀约。');
        //已成单数量判断
        $orders_count = $this->orders_dao->countData(array(
            'u_id' => $worker_result['t_author'],
            't_id' => $worker_result['t_id'],
            'tew_id' => $worker_result['tew_id'],
            's_id' => $worker_result['tew_skills'],
            'where' => 'o_confirm > 0 and o_status in (0,1,-1,-2,-3)'));
        if ($orders_count >= $worker_result['tew_worker_num']) $this->exportData('工人数量已足，无法成单。');

        $curtime = time();
        $result = $this->orders_dao->addData(array(
            't_id' => $worker_result['t_id'],
            'u_id' => $worker_result['t_author'],
            'o_worker' => $data['o_worker'],
            'o_amount' => $worker_result['tew_price'],
            'o_in_time' => $curtime,
            'o_last_edit_time' => $curtime,
            'tew_id' => $worker_result['tew_id'],
            's_id' => $worker_result['tew_skills'],
            'o_sponsor' => $data['o_sponsor'],
        ));
        if (!$result)
        {
            $this->exportData('failure');
        }

        //站内信通知
        $this->msgToUser(array(
            'author' => 0,
            'type'   => 1,
            'status' => 1,
            'to_uid' => ($data['o_sponsor'] == $data['o_worker']) ? $worker_result['t_author'] : $data['o_worker'],
            'title'  => ($data['o_sponsor'] == $data['o_worker']) ? '【任务：有工人接单】' : '【任务：邀约成功】',
            'desc'   => ($data['o_sponsor'] == $data['o_worker']) ? '有工人接单，请进入雇主发布管理中查看' : '邀约成功，请进入工人工作管理中查看',
        ));

        //成单即更改订单状态为洽谈中
        $task_dao = new \WDAO\Tasks();
        $task_dao->updateData(array(
            't_status' => 1
        ),array(
            't_id' => $worker_result['t_id'],
            't_status' => 0,
        ));

        $this->exportData('success');
    }

    /**
     * 订单雇主改价
     */
    private function price()
    {
        //$this->db->debug = 1;
        $data = $tmp = $task_param = array();
        //任务工人关系id
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = $task_param['tew_id'] = intval($_REQUEST['tew_id']);
        //任务id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $tmp['t_id'] = intval($_REQUEST['t_id']);
        //雇主id
        if (isset($_REQUEST['t_author']) && intval($_REQUEST['t_author']) > 0) $data['t_author'] = intval($_REQUEST['t_author']);
        //传递单价
        if (isset($_REQUEST['amount']) && intval($_REQUEST['amount']) > 0) $data['tew_price'] = floatval($_REQUEST['amount']);
        //工人数量
        if (isset($_REQUEST['worker_num']) && intval($_REQUEST['worker_num']) > 0) $data['worker_num'] = intval($_REQUEST['worker_num']);
        //开始时间
        if (isset($_REQUEST['start_time']) && intval($_REQUEST['start_time']) > 0) $data['start_time'] = strtotime($_REQUEST['start_time']);
        //结束时间
        if (isset($_REQUEST['end_time']) && intval($_REQUEST['end_time']) > 0) $data['end_time'] = strtotime($_REQUEST['end_time']);
        //工人id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']);
        //print_r($data);exit;
        if (!empty($data) && isset($data['tew_id']) && $data['tew_id'] > 0 &&
            isset($tmp['t_id']) && $tmp['t_id'] > 0 &&
            isset($data['t_author']) && $data['t_author'] > 0 &&
            isset($data['tew_price']) && $data['tew_price'] > 0 &&
            isset($data['worker_num']) && $data['worker_num'] > 0 &&
            isset($data['o_worker']) && $data['o_worker'] > 0)
        {
            //print_r($data);exit;
            //1：根据条件 获取该条订单信息
            $task_dao = new \WDAO\Task_ext_worker();
            $task_param['join'] = array('tasks', 'tasks.t_id = task_ext_worker.t_id');
            $task_param['where'] = ' task_ext_worker.t_id = "' . $tmp['t_id'] . '" and task_ext_worker.tew_id = "' . $data['tew_id'] . '" and tasks.t_author = "' . $data['t_author'] . '"';
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
                    'where' => 'o_status != -4',
                    'pager' => 0,
                ));

                if (empty($orders_data['data']))
                {
                    $this->exportData('数据异常');
                }

                $tmp['workers'] = $tmp['confirm'] = array();
                foreach ($orders_data['data'] as $key => $val)
                {
                    if (isset($data['o_worker']) && isset($val['o_worker']) && $val['o_worker'] == $data['o_worker'] && $val['o_confirm'] == 1)
                    {
                        $this->exportData('工作中不能改价');
                    }

                    if (isset($val['o_worker']) && $val['o_worker'] > 0)
                    {
                        $tmp['workers'][$key] = $val['o_worker'];
                    }

                    if (isset($val['o_confirm']) && $val['o_confirm'] == 1)
                    {
                        $tmp['confirm'][$key] = $val['confirm'];
                    }

                    if (isset($val['o_amount']) && $val['o_worker'] == $data['o_worker'] &&
                        isset($val['tew_id']) && $data['tew_id'] == $val['tew_id'] &&
                        isset($val['t_id']) && $tmp['t_id'] == $val['t_id'])
                    {
                        $tmp['original_amount'] = $val['o_amount'];
                    }
                }
                unset($key, $val);

                if (!in_array($data['o_worker'], $tmp['workers']) || !isset($tmp['original_amount']))
                {
                    $this->exportData('数据异常');
                }
                if (count($tmp['confirm']) > 0 && $data['worker_num'] < count($tmp['confirm']) + 1)
                {
                    $this->exportData('已有工人开工，不能减少工人数量');
                }

                //2：将取出来的信息 与 要改的信息做比对 得出差额
                //改价前后差价 = 原价 - 改后价
                $tmp['agio'] = $tmp['original_amount'] - $data['tew_price'];
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
                //任务更新改价次数及总任务改后价
                $tmp['edit_amount'] = ($data['tew_price'] * $data['worker_num'] * ceil(($data['end_time'] - $data['start_time']) / 3600 / 24));
                $times_update = $task_dao->queryData('update tasks set t_amount_edit_times=t_amount_edit_times+1, t_amount=t_amount + ' . ($tmp['edit_amount'] * -1) . ', t_last_edit_time = ' . time() . ', t_last_editor = ' . $data['t_author'] . ' where t_id = "' . $tmp['t_id'] . '"');

                //更改该工人的订单价格
                $order_info = $this->orders_dao->listData(array(
                    't_id' => $tmp['t_id'],
                    'tew_id' => $data['tew_id'],
                    'o_worker' => $data['o_worker'],
                    's_id' => $task_data['tew_skills'],
                    'o_status' => 0,
                    'pager' => 0,
                ));

                $orders_update = false;
                if (!empty($order_info['data'][0]))
                {
                    $orders_update = $this->orders_dao->updateData(array(
                        'o_amount' => $data['tew_price'],
                        'o_confirm' => 0,
                        'o_last_edit_time' => time(),
                        'o_start_time' => $data['start_time'],
                        'o_end_time' => $data['end_time'],
                    ),array(
                        'o_id' => $order_info['data'][0]['o_id'],
                    ));
                    if ($orders_update)
                    {
                        //站内信通知
                        $this->msgToUser(array(
                            'author' => 0,
                            'type'   => 1,
                            'status' => 1,
                            'to_uid' => $data['o_worker'],
                            'title'  => '【任务：价格变更】',
                            'desc'   => '价格变更，请进入工人工作管理中查看',
                        ));
                    }
                }

                //4：多退少补 操作平台与用户资金
                $user_funds_result = $this->userFunds($data['t_author'], $tmp['edit_amount'], $type = 'changeprice'); //用户资金
                $platform_funds_result = $this->platformFundsLog($tmp['t_id'], (-1 * $tmp['edit_amount']), 3, 'changeprice');     //平台资金日志

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
     * 解雇工人或工人辞职
     */
    private function unbind()
    {
        //$this->db->debug = 1;
        $data = array();
        //任务工人关系id
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = intval($_REQUEST['tew_id']);
        //任务id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']);
        //方式 0解雇工人 1工人辞职
        $tmp['type'] = (isset($_REQUEST['type']) && in_array(trim($_REQUEST['type']), array('fire', 'resign'))) ? $_REQUEST['type'] : '';
        if ('' == $tmp['type']) $this->exportData('参数错误');
        //工人id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']);
        //雇主id
        if (isset($_REQUEST['u_id']) && intval($_REQUEST['u_id']) > 0) $data['u_id'] = intval($_REQUEST['u_id']);
        //技能id
        if (isset($_REQUEST['s_id']) && intval($_REQUEST['s_id']) > 0) $data['s_id'] = intval($_REQUEST['s_id']);
        //订单id
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = intval($_REQUEST['o_id']);
        //星级
        if (isset($_REQUEST['start']) && intval($_REQUEST['start']) >= 0) $tmp['start'] = intval($_REQUEST['start']);
        //评价内容
        if (isset($_REQUEST['appraisal']) && trim($_REQUEST['appraisal']) != '') $tmp['appraisal'] = trim($_REQUEST['appraisal']);

        if (!empty($data) && isset($data['tew_id']) && isset($data['t_id']) && isset($data['o_worker']) && isset($data['s_id']) && isset($data['u_id']))
        {
            //根据参数获取订单信息
            $order_param = $data;
            $order_param['pager'] = 0;
            $order_data = $this->orders_dao->listData($order_param);
            unset($order_param);
            //比对订单数量
            if (count($order_data['data']) < 1)
            {
                $this->exportData('订单不存在');
            }
            $data['o_id'] = isset($order_data['data'][0]['o_id']) ? $order_data['data'][0]['o_id'] : 0; //赋值订单id
            if ($data['o_id'] < 1)
            {
                $this->exportData('数据异常');
            }

            //更新订单状态
            $result = $this->orders_dao->updateData(array('o_status' => ($tmp['type'] == 'fire') ? -2 : -1, 'unbind_time' => time()), $data);
            if (!$result)
            {
                $this->exportData('failure');
            }

            //获取该任务下该工种是否唯一
            $task_worker_dao = new \WDAO\Task_ext_worker();
            $task_worker_count = $task_worker_dao->countData(array('t_id' => $data['t_id'], 'tew_worker_num' => 1));
            if ($task_worker_count == 1)
            {
                //$this->payout();
                $pay_param = $data;
                $pay_param['t_author'] = $data['u_id'];
                $result = $this->_payOut($pay_param);
                if (!$result)
                {
                    $this->exportData('订单已更新，支付失败');
                }

            }
            else
            {
                $result = $this->_resetWorker($data['o_worker']); //释放工人
                if (!$result)
                {
                    $this->exportData('工人释放失败');
                }
                $this->_resetTask($data['t_id']); //变更任务状态
            }

            //评价块
            if (!empty($tmp) && isset($tmp['type']) && isset($tmp['start']) && isset($tmp['appraisal']) && ($tmp['start'] >= 0 || $tmp['appraisal'] != ''))
            {
                $comment_dao = new \WDAO\Task_comment();
                $comment_dao->addComment(array(
                    't_id' => $data['t_id'],
                    'u_id' => ($tmp['type'] == 'fire') ? $data['u_id'] : $data['o_worker'],
                    'tc_u_id' => ($tmp['type'] == 'fire') ? $data['o_worker'] : $data['u_id'],
                    'start' => $tmp['start'],
                    'desc' => $tmp['appraisal'],
                ));
            }

            //站内信通知
            $this->msgToUser(array(
                'author' => 0,
                'type'   => 1,
                'status' => 1,
                'to_uid' => ($tmp['type'] == 'fire') ? $data['o_worker'] : $data['u_id'],
                'title'  => ($tmp['type'] == 'fire') ? '【任务：解除关系】' : '【任务：工人辞职】',
                'desc'   => ($tmp['type'] == 'fire') ? '解除关系，请进入工人工作管理中查看' : '解除关系，请进入雇主发布管理中查看',
            ));

            $this->exportData('success');
        }
        $this->exportData('数据异常');
    }

    /**
     * 取消订单
     */
    private function cancel()
    {
        //$this->db->debug = 1;
        $data = $task_param = $tmp = array();
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = $task_param['o_id'] = intval($_REQUEST['o_id']);
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = $task_param['tew_id'] = intval($_REQUEST['tew_id']);
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = $task_param['t_id'] = intval($_REQUEST['t_id']);
        if (isset($_REQUEST['u_id']) && intval($_REQUEST['u_id']) > 0) $data['u_id'] = $task_param['t_author'] = intval($_REQUEST['u_id']);
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = $task_param['tewo_worker'] = intval($_REQUEST['o_worker']);
        if (isset($_REQUEST['s_id']) && intval($_REQUEST['s_id']) > 0) $data['s_id'] = $task_param['s_id'] = intval($_REQUEST['s_id']);
        if (isset($_REQUEST['o_confirm']) && is_numeric($_REQUEST['o_confirm'])) $data['o_confirm'] = intval($_REQUEST['o_confirm']);
        if (isset($_REQUEST['o_status']) && is_numeric($_REQUEST['o_status'])) $data['o_status'] = intval($_REQUEST['o_status']);
        if (isset($_REQUEST['sponsor']) && intval($_REQUEST['sponsor']) > 0) $tmp['sponsor'] = intval($_REQUEST['sponsor']);

        if (!empty($data) && (isset($data['o_id']) || (isset($data['tew_id']) && isset($data['t_id']) && isset($data['u_id']) && isset($data['o_worker']) && isset($data['s_id']))))
        {
            $data['pager'] = 0;
            $data['limit'] = 1;
            $data['order'] = 'o_id desc';
            $orders = $this->orders_dao->listData($data);
            if (!empty($orders['data'][0]))
            {
                $data['o_id'] = $orders['data'][0]['o_id'];
                $data['t_id'] = $orders['data'][0]['t_id'];
            }
            else
            {
                $this->exportData('failure');
            }

            $result = $this->orders_dao->updateData(array('o_status' => -4), $data);
            if ($result)
            {
                if (!empty($tmp) && isset($tmp['sponsor']) && intval($tmp['sponsor']) > 0)
                {
                    //站内信通知
                    $this->msgToUser(array(
                        'author' => 0,
                        'type'   => 1,
                        'status' => 1,
                        'to_uid' => ($tmp['sponsor'] == $orders['data'][0]['o_worker']) ? $orders['data'][0]['u_id'] : $orders['data'][0]['o_worker'],
                        'title'  => '【任务：订单取消】',
                        'desc'   => ($tmp['sponsor'] == $orders['data'][0]['o_worker']) ? '订单取消，请进入雇主发布管理中查看' : '订单取消，请进入工人工作管理中查看',
                    ));
                }
                //任务状态变更
                $this->_resetTask($data['t_id']);
                $this->exportData('success');
            }
        }
        $this->exportData('failure');
    }

    /**
     * 订单支付
     */
    private function payout()
    {
        //$this->db->debug = 1;
        $data = array();
        //任务工人关系id 即单个工种的id
        if (isset($_REQUEST['tew_id']) && intval($_REQUEST['tew_id']) > 0) $data['tew_id'] = intval($_REQUEST['tew_id']);
        //任务id
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']);
        //雇主id
        if (isset($_REQUEST['t_author']) && intval($_REQUEST['t_author']) > 0) $data['t_author'] = intval($_REQUEST['t_author']);

        if (!$this->_payOut($data))
        {
            $this->exportData('failure');
        }
        $this->_resetTask($data['t_id']);
        $this->exportData('successs');
    }

    /**
     * 工人删除订单
     */
    private function del2()
    {
        $data = array();
        //订单id
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0) $data['o_id'] = intval($_REQUEST['o_id']);
        //工人id
        if (isset($_REQUEST['o_worker']) && intval($_REQUEST['o_worker']) > 0) $data['o_worker'] = intval($_REQUEST['o_worker']);
        if (!empty($data) && isset($data['o_id']) && isset($data['o_worker']))
        {
            $result = $this->orders_dao->updateData(array('o_status' => -9), $data);
            if ($result)
            {
                $this->exportData('success');
            }
        }
        $this->exportData('failure');
    }

    /**
     * 释放工人
     */
    protected function _resetWorker($worker_id)
    {
        if ($worker_id > 0)
        {
            $user_dao = new \WDAO\Users(array('table' => 'users'));
            return $user_dao->taskStatus($worker_id, '0');
        }
        return false;
    }

    /**
     * 支付
     */
    protected function _payOut($data = array())
    {
        if (!empty($data) && isset($data['t_author']) && isset($data['t_id']) && isset($data['tew_id']))
        {
            //先获取任务信息
            $task_dao = new \WDAO\Tasks();
            $data['pager'] = 0;
            $task_data = $task_dao->listData($data);
            if (!empty($task_data['data'][0]))
            {
                $order_param = array();
                //获取该任务所属的全部需要支付订单信息
                $order_param['where'] = 'orders.o_confirm in (1, 2) and task_ext_worker.tew_status = 0 and orders.o_pay = 0';
                $order_param['join'] = array('task_ext_worker', 'task_ext_worker.tew_id = orders.tew_id and task_ext_worker.tew_skills = orders.s_id and task_ext_worker.t_id = orders.t_id');
                $order_param['fields'] = 'task_ext_worker.tew_id, task_ext_worker.tew_skills, task_ext_worker.tew_worker_num, task_ext_worker.tew_price, task_ext_worker.tew_start_time, task_ext_worker.tew_end_time,
                orders.o_id, orders.o_confirm, orders.t_id, orders.u_id, orders.o_worker, orders.o_amount, orders.o_in_time, orders.o_status, orders.o_pay, orders.unbind_time';
                $order_param['where'] .= ' and orders.t_id = "' . intval($data['t_id']) . '" and orders.u_id = "' . $data['t_author'] . '"';
                if (isset($data['tew_id']))
                {
                    $order_param['where'] .= ' and orders.tew_id = "' . $data['tew_id'] . '" and orders.o_status in (0, -1, -2) ';
                }
                $order_param['pager'] = 0;
                $orders_data = $this->orders_dao->listData($order_param);
                //print_r($orders_data);exit;
                if (!empty($orders_data['data']))
                {
                    $pay_status = 1;
                    $this->db->start();
                    foreach ($orders_data['data'] as $key => $val)
                    {
                        if (!empty($val) && isset($val['o_id']) && $val['o_id'] > 0 &&
                            isset($val['o_amount']) && $val['o_amount'] > 0 &&
                            isset($val['o_worker']) && $val['o_worker'] > 0 &&
                            isset($val['o_status']) && in_array($val['o_status'], array(0, -1, -2)) &&
                            isset($val['o_confirm']) && $val['o_confirm'] == 1 &&
                            isset($val['o_pay']) && $val['o_pay'] == 0)
                        {
                            //print_r($val);
                            //continue;
                            $platform_rate = isset($this->web_config['charge_rate']) && $this->web_config['charge_rate'] > 0 ? $this->web_config['charge_rate'] : 0;
                            if ($platform_rate <= 0)
                            {
                                $platform_rate = 0;
                            }
                            //原有该花总价
                            $original_amount = $val['o_amount'] * (ceil($val['tew_end_time'] - $val['tew_start_time']) / 3600 / 24 + 1);
                            $original_amount = $original_amount - $original_amount * $platform_rate;

                            //解决辞职或解雇的工人价格
                            if ($val['o_status'] == -1) //辞职
                            {
                                //实际总价
                                $real_total = $val['o_amount'] * (ceil($val['unbind_time'] - $val['tew_start_time']) / 3600 / 24);
                                $real_total = $real_total - $real_total * $platform_rate;
                                if ($original_amount > $real_total)
                                {
                                    $platform_result = $this->platformFundsLog($val['o_id'], (($original_amount - $real_total) * -1), 0, 'payorder'); //平台资金支出
                                    $user_funds_result = $this->userFunds($val['o_worker'], ($original_amount - $real_total), 'overage'); //雇主用户资金收入
                                    if (!$platform_result || !$user_funds_result)
                                    {
                                        $pay_status = 0;
                                    }
                                    $original_amount = $real_total;
                                }
                            }
                            if ($val['o_status'] == -2) //解雇
                            {
                                //实际总价
                                $real_total = $val['o_amount'] * (ceil($val['unbind_time'] - $val['tew_start_time']) / 3600 / 24 + 1);
                                $real_total = $real_total - $real_total * $platform_rate;
                                if ($original_amount > $real_total)
                                {
                                    $platform_result = $this->platformFundsLog($val['o_id'], (($original_amount - $real_total) * -1), 0, 'payorder'); //平台资金支出
                                    $user_funds_result = $this->userFunds($val['o_worker'], ($original_amount - $real_total), 'overage'); //雇主用户资金收入
                                    if (!$platform_result || !$user_funds_result)
                                    {
                                        $pay_status = 0;
                                    }
                                    $original_amount = $real_total;
                                }
                            }

                            if ($pay_status == 1)
                            {
                                //给每个工人单独发钱并单独扣除平台款项
                                $platform_result = $user_result = 0;
                                $platform_result = $this->platformFundsLog($val['o_id'], ($original_amount * -1), 0, 'payorder'); //平台资金支出
                                $user_funds_result = $this->userFunds($val['o_worker'], $original_amount, 'overage'); //工人用户资金收入
                                $user_result = $this->_resetWorker($val['o_worker']); //释放工人状态
                                $pay = $this->orders_dao->payStatus($val['o_id'], '1'); //更新订单支付状态
                                if (!$platform_result || !$user_funds_result || !$user_result || !$pay) {
                                    $pay_status = 0;
                                }
                                else
                                {
                                    //站内信通知
                                    $this->msgToUser(array(
                                        'author' => 0,
                                        'type'   => 1,
                                        'status' => 1,
                                        'to_uid' => $val['o_worker'],
                                        'title'  => '【任务：订单结算】',
                                        'desc'   => '订单结算，请进入工人工作管理中查看',
                                    ));
                                }
                            }
                        }
                        else
                        {//正在洽谈中的 直接取消用户
                            if (!empty($val) && isset($val['o_id']) && $val['o_id'] > 0 &&
                                isset($val['o_amount']) && $val['o_amount'] > 0 &&
                                isset($val['o_worker']) && $val['o_worker'] > 0 &&
                                isset($val['o_status']) && in_array($val['o_status'], array(0, -1, -2)) &&
                                isset($val['o_confirm']) && $val['o_confirm'] == 2 &&
                                isset($val['o_pay']) && $val['o_pay'] == 0)
                            {
                                $this->orders_dao->updateData(array('o_status' => -4), $val);
                                //站内信通知
                                $this->msgToUser(array(
                                    'author' => 0,
                                    'type'   => 1,
                                    'status' => 0,
                                    'to_uid' => $val['o_worker'],
                                    'title'  => '【任务：订单取消】',
                                    'desc'   => '订单取消，请进入工人工作管理中查看',
                                ));
                            }
                        }
                    }
                    unset($key, $val);

                    if ($pay_status == 1)
                    {
                        $task_worker_dao = new \WDAO\Task_ext_worker();
                        $task_worker_result = $task_worker_dao->updateData(array('tew_status' => 1), $data); //任务的工种状态变更
                        if ($task_worker_result)
                        {
                            //获取该任务下的全部工种状态
                            $task_result = 1;

                            $task_worker_count = $task_worker_dao->countData(array(
                                't_id' => $data['t_id'],
                                'tew_status' => 0
                            ));
                            if ($task_worker_count == 0) //如果全部工种都完成了 那么将任务设置为完成
                            {
                                $task_result = $task_dao->updateData(array('t_status' => 3), $data);
                            }

                            if ($task_result)
                            {
                                $this->db->commit();
                                return true;
                            }
                        }
                    }
                    $this->db->rollback();
                }
            }
        }
        return false;
    }

    /**
     * 任务置空
     */
    protected function _resetTask($t_id)
    {
        //$this->db->debug = 1;
        if (intval($t_id) > 0)
        {
            $tasks_dao = new \WDAO\Tasks();
            return $tasks_dao->resetTaskByLastWork($t_id);
        }
        return false;
    }
}