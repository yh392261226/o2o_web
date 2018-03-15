<?php
/**
 * 订单接口
 */
namespace App\Controller;

class Crond extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->orders_dao = new \WDAO\Orders();
        $this->tasks_dao = new \WDAO\Tasks();
        $this->task_ext_worker = new \WDAO\Task_ext_worker();
        //$this->db->debug = 1;
    }

    public function index()
    {
        $action = (isset($_REQUEST['action']) && '' != trim($_REQUEST['action'])) ? trim($_REQUEST['action']) : 'info';
        if ('' != trim($action)) {
            $this->$action();
        }
    }

    /**
     * 过期处理
     */
    private function expiredTask()
    {
//        $this->db->debug = 1;
        $param = array();
//        $starttime = strtotime(date("Y-m-d",strtotime("-1 day"))); //昨天0点时间
        $endtime = strtotime(date("Y-m-d",time())); //今天0点时间
        $param['pager'] = 0;
        $param['where'] = ' tasks.t_status in (0, 1, 2, 3, 4, 5)';
//        $param['where'] .= ' and task_ext_worker.tew_end_time >= ' . $starttime;
        $param['where'] .= ' and task_ext_worker.tew_end_time < ' . $endtime;
        $param['leftjoin'] = array('task_ext_worker', 'tasks.t_id = task_ext_worker.t_id');
        $param['fields'] = 'tasks.*, task_ext_worker.*';
        $tasks_data = $this->tasks_dao->getTasksWithOrders($param);
        if (!empty($tasks_data))
        {
            $tmp = array();
            //处理原始数据到想要的结果集
            foreach ($tasks_data as $key => $val)
            {
                $tmp[$val['t_id']]['t_id'] = $val['t_id'];
                $tmp[$val['t_id']]['r_province'] = $val['r_province'];
                $tmp[$val['t_id']]['r_city'] = $val['r_city'];
                $tmp[$val['t_id']]['r_area'] = $val['r_area'];
                $tmp[$val['t_id']]['t_title'] = $val['t_title'];
                $tmp[$val['t_id']]['t_author'] = $val['t_author'];
                $tmp[$val['t_id']]['t_status'] = $val['t_status'];
                $tmp[$val['t_id']]['bd_id'] = $val['bd_id'];
                $tmp[$val['t_id']]['t_storage'] = $val['t_storage'];
                $tmp[$val['t_id']]['t_type'] = $val['t_type'];
                $tmp[$val['t_id']]['t_phone_status'] = $val['t_phone_status'];
                $tmp[$val['t_id']]['t_phone'] = $val['t_phone'];
                $tmp[$val['t_id']]['t_amount_edit_times'] = $val['t_amount_edit_times'];
                $tmp[$val['t_id']]['t_amount'] = $val['t_amount'];
                $tmp[$val['t_id']]['t_edit_amount'] = $val['t_edit_amount'];
                $tmp[$val['t_id']]['t_duration'] = $val['t_duration'];
                $tmp[$val['t_id']]['t_info'] = $val['t_info'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_id'] = $val['tew_id'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_skills'] = $val['tew_skills'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_worker_num'] = $val['tew_worker_num'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_price'] = $val['tew_price'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_start_time'] = $val['tew_start_time'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_end_time'] = $val['tew_end_time'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_status'] = $val['tew_status'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_type'] = $val['tew_type'];
                $tmp[$val['t_id']]['workers'][$val['tew_id']]['tew_lock'] = $val['tew_lock'];
                $tmp[$val['t_id']]['orders'] = $val['orders'];
            }
            unset($key, $val);
            if (!empty($tmp))
            {
                unset($tasks_data);
                $tasks_data = array_values($tmp);
                unset($tmp);
            }
//print_r($tasks_data);exit;
            foreach ($tasks_data as $key => $val)
            {
                if (isset($val['orders']) && empty($val['orders']))
                {
                    //完全无订单  直接删除任务
                    $platform_rate = isset($this->web_config['charge_rate']) && $this->web_config['charge_rate'] > 0 ? $this->web_config['charge_rate'] : 0;
                    if ($platform_rate <= 0)
                    {
                        $platform_rate = 0;
                    }

                    $this->db->start();
                    //归还已经扣除资金
                    $platform_funds_dao = new \WDAO\Platform_funds_log();
                    $back_platform_funds = $platform_funds_dao->rebackFundsToUser(array(
                        'pfl_type' => 3,
                        'pfl_reason' => '"pubtask","changeprice","taskreturn"',
                        'pfl_type_id' => $val['t_id'],
                        'u_id' => $val['t_author'],
                        'platform_rate' => $platform_rate,
                    ));

                    //删除之前的该任务
                    $del_old_result = $this->tasks_dao->updateData(array('t_status' => -9), array('t_id' => $val['t_id'], 't_author' => $val['t_author']));
                    if (!$back_platform_funds || !$del_old_result)
                    {
                        $this->db->rollback();
                        continue;
                    }
                    $this->db->commit();

                }
                else
                {
                    //确认支付
                    $pay_status = 1;
                    $this->db->start();
                    foreach ($val['orders'] as $o_k => $o_v)
                    {
                        //print_r($val);exit;
                        //原该花费的总价
                        $origin_cost_total = $val['workers'][$o_v['tew_id']]['tew_worker_num'] * $val['workers'][$o_v['tew_id']]['tew_price'] * getDays($val['workers'][$o_v['tew_id']]['tew_start_time'], $val['workers'][$o_v['tew_id']]['tew_end_time']);
                        if (!empty($o_v) && isset($o_v['o_id']) && $o_v['o_id'] > 0 &&
                            isset($o_v['o_amount']) && $o_v['o_amount'] > 0 &&
                            isset($o_v['o_worker']) && $o_v['o_worker'] > 0 &&
                            isset($o_v['o_status']) && in_array($o_v['o_status'], array(0, -1, -2)) &&
                            isset($o_v['o_confirm']) && $o_v['o_confirm'] == 1 &&
                            isset($o_v['o_pay']) && $o_v['o_pay'] == 0)
                        {
                            $platform_rate = isset($this->web_config['charge_rate']) && $this->web_config['charge_rate'] > 0 ? $this->web_config['charge_rate'] : 0;
                            if ($platform_rate <= 0)
                            {
                                $platform_rate = 0;
                            }
                            //订单中原有该花总价
                            $original_amount = $o_v['o_amount'] * getDays($val['workers'][$o_v['tew_id']]['tew_start_time'], $val['workers'][$o_v['tew_id']]['tew_end_time']);
                            $origin_cost_total = $origin_cost_total - ($val['workers'][$o_v['tew_id']]['tew_price'] * getDays($val['workers'][$o_v['tew_id']]['tew_start_time'], $val['workers'][$o_v['tew_id']]['tew_end_time'])) + $original_amount; //订单修改后的当前工种的总价

                            //解决辞职或解雇的工人价格
                            if ($o_v['o_status'] == -1) //辞职
                            {
                                //实际总价
                                $real_total = $o_v['o_amount'] * (getDays($val['workers'][$o_v['tew_id']]['tew_start_time'], $o_v['unbind_time']) - 1);
                                $original_amount = $real_total;
                            }
                            elseif ($val['o_status'] == -2) //解雇
                            {
                                //实际总价
                                $real_total = $o_v['o_amount'] * getDays($val['workers'][$o_v['tew_id']]['tew_start_time'], $o_v['unbind_time']);
                                $original_amount = $real_total;
                            }

                            //给每个工人单独发钱并单独扣除平台款项
                            $platform_result = $user_result = 0;
                            $origin_cost_total = $origin_cost_total - $original_amount; //去掉这单后 实际总价剩余
                            $original_amount = $original_amount - $original_amount * $platform_rate;
                            $platform_result = $this->platformFundsLog($o_v['o_id'], ($original_amount * -1), 0, 'payorder'); //平台资金支出
                            $user_funds_result = $this->userFunds($o_v['o_worker'], $original_amount, 'overage'); //工人用户资金收入
                            $user_result = $this->_resetWorker($o_v['o_worker']); //释放工人状态
                            $pay = $this->orders_dao->payStatus($o_v['o_id'], '1', $o_v['o_status']); //更新订单支付状态
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
                                    'to_uid' => $o_v['o_worker'],
                                    'title'  => '【任务：订单结算】',
                                    'desc'   => '订单结算，请进入工人工作管理中查看',
                                ));
                            }

                        }
                        else
                        {//正在洽谈中的 直接取消用户
                            if (!empty($o_v) && isset($o_v['o_id']) && $o_v['o_id'] > 0 &&
                                isset($o_v['o_amount']) && $o_v['o_amount'] > 0 &&
                                isset($o_v['o_worker']) && $o_v['o_worker'] > 0 &&
                                isset($o_v['o_status']) && $o_v['o_status'] == 0 &&
                                isset($o_v['o_confirm']) && $o_v['o_confirm'] == 2 &&
                                isset($o_v['o_pay']) && $o_v['o_pay'] == 0)
                            {
                                $this->orders_dao->updateData(array('o_status' => -4), array('o_id' ));
                                //站内信通知
                                $this->msgToUser(array(
                                    'author' => 0,
                                    'type'   => 1,
                                    'status' => 0,
                                    'to_uid' => $o_v['o_worker'],
                                    'title'  => '【任务：订单取消】',
                                    'desc'   => '订单取消，请进入工人工作管理中查看',
                                ));
                            }
                        }
                    }
                    unset($o_k, $o_v);

                    if ($pay_status == 1)
                    {
                        $task_worker_dao = new \WDAO\Task_ext_worker();
                        $task_worker_result = $task_worker_dao->updateData(array('tew_status' => 1), array('tew_id' => $o_v['tew_id'])); //任务的工种状态变更
                        $platform_result = $this->platformFundsLog(intval($val['t_id']), ($origin_cost_total * -1), 3, 'taskreturn'); //平台资金支出
                        $user_funds_result = $this->userFunds(intval($val['t_author']), $origin_cost_total, 'overage'); //雇主用户资金收入
                        if ($task_worker_result && $platform_result && $user_funds_result)
                        {
                            //获取该任务下的全部工种状态
                            $task_result = 1;

                            $task_worker_count = $task_worker_dao->countData(array(
                                't_id' => intval($val['t_id']),
                                'tew_status' => 0
                            ));
                            if ($task_worker_count == 0) //如果全部工种都完成了 那么将任务设置为完成
                            {
                                $task_result = $task_dao->updateData(array('t_status' => 3), array('t_id' => intval($val['t_id'])));
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

                //站内信通知
                $this->msgToUser(array(
                    'author' => 0,
                    'type'   => 1,
                    'status' => 1,
                    'to_uid' => $val['t_author'],
                    'title'  => '【任务：过期处理】',
                    'desc'   => '您的任务：#' . $val['t_title'] . '# 已过期，资金在扣除工人部分后已结算反还。',
                ));
            }
        }
        return false; //无任何可操作数据
    }
}