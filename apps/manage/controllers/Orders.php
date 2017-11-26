<?php
namespace App\Controller;

class Orders extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->orders_dao = new \MDAO\Orders();
        //$this->db->debug = 1;
    }

    public function cStatus()
    {
        $is_ajax = isset($_REQUEST['is_ajax']) ? intval($_REQUEST['is_ajax']) : 0;
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0)
        {
            $status = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : '';
            if ('' === $status)
            {
                if ($is_ajax)
                {
                    echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
                }
                msg('操作失败:参数错误', 0);
            }

            $status_data = array('o_status' => $status);
            if ($status == -3)
            {
                $status_data['o_dispute_time'] = time();
            }

            $result = $this->orders_dao->updateData($status_data, array('o_id' => intval($_REQUEST['o_id'])));

            if (!$result)
            {
                if ($is_ajax)
                {
                    echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
                }
                msg('操作失败', 0);
            }
            //判断该任务是否还有其他纠纷订单 没有就把该任务的状态设置为纠纷解决或纠纷中
            //if (in_array(intval($_REQUEST['status']), array(2, -3)))
            //{
            //    $orders_info = $this->orders_dao->infoData(intval($_REQUEST['o_id']));
            //    if (!empty($orders_info))
            //    {
            //        $dispute_orders = $this->orders_dao->countData(array('t_id' => $orders_info['t_id'], 'o_status' => intval($_REQUEST['status'])));
            //        if ($dispute_orders <= 0)
            //        {
            //            $task_dao = new \MDAO\Tasks();
            //            $task_dao->updateData(array('t_status' => '4'), array('t_id' => $orders_info['t_id']));
            //        }
            //    }
            //}
            $log_dao = new \MDAO\Orders_log();
            $log_data = array(
                'o_id' => intval($_REQUEST['o_id']),
                't_id' => intval($_REQUEST['t_id']) > 0 ? intval($_REQUEST['t_id']) : 0,
                'ol_remark' => 'changestatus:' . $status,
                'ol_manager' => self::$manager_status,
                'ol_in_time' => time(),
            );
            $log_dao->addData($log_data);

            if ($is_ajax)
            {
                echo json_encode(array('msg' => '操作成功', 'status' => 1));exit;
            }
            msg('操作成功', 1);
        }
        if ($is_ajax)
        {
            echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
        }
        msg('操作失败', 0);
    }

    public function payByType()
    {
        $is_ajax = isset($_REQUEST['is_ajax']) ? intval($_REQUEST['is_ajax']) : 0;
        $o_id = isset($_REQUEST['o_id']) ? intval($_REQUEST['o_id']) : 0;
        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : '';
        if ($o_id > 0 && in_array($type, array(0, 1)))
        {
            $order_param['pager'] = 0;
            $order_param['fields'] = 'orders.*, task_ext_worker.*';
            $order_param['where'] = 'orders.o_id = "' . $o_id . '" and orders.o_status = 2 and orders.o_pay = 0';
            $order_param['join'] = array('task_ext_worker', 'orders.tew_id = task_ext_worker.tew_id and orders.t_id = task_ext_worker.t_id');

            $info = $this->orders_dao->listData($order_param);
            if (!empty($info['data'][0]))
            {
                $info = $info['data'][0]; //订单详情

                //获取平台手续费
                $platform_rate = isset($this->web_config['charge_rate_m']) && $this->web_config['charge_rate_m'] > 0 ? $this->web_config['charge_rate_m'] : 0;
                if ($platform_rate <= 0)
                {
                    $platform_rate = 0;
                }
                $pay_amount = 0;
                switch ($type)
                {
                    case '0': //辞职
                        $pay_amount = $info['o_amount'] / (ceil($val['unbind_time'] - $val['tew_start_time']) / 3600 / 24);
                        break;
                    case '1': //解雇
                        $pay_amount = $info['o_amount'] / (ceil($val['unbind_time'] - $val['tew_start_time']) / 3600 / 24 + 1);
                        break;
                    case '2': //全款
                        $pay_amount = $info['o_amount'] / (ceil($info['tew_end_time'] - $info['tew_start_time']) / 3600 / 24 + 1);
                        break;
                }
                $pay_amount = $pay_amount - $pay_amount * $platform_rate; //实际总价(扣除手续费后)

                $this->db->start();
                //扣除平台资金
                $platform_dao = new \MDAO\Platform_funds_log();
                $platform_result = $platform_dao->addData(array(
                    'pfl_type' => 4,
                    'pfl_type_id' => $o_id,
                    'pfl_amount' => (-1 * $pay_amount),
                    'pfl_in_time' => time(),
                    'pfl_reason' => 'payworker',
                    'pfl_status' => 0,
                ));
                if ($platform_result)
                {
                    $user_dao = new \MDAO\Users();
                    $user_funds_result = $user_dao->queryData('update users_ext_funds set uef_overage = uef_overage + ' . $pay_amount . ' where u_id = "' . $info['o_worker'] . '"');
                    $pay_result = $this->orders_dao->updateData(array('o_pay' => 1, 'o_pay_time' => time()), array('o_id' => $o_id));
                    $user_result = $user_dao->updateData(array('u_task_status' => 0), array('u_id' => $info['o_worker'])); //释放工人
                    if ($user_funds_result && $user_result && $pay_result)
                    {
                        $this->db->commit();
                        $log_data = array(
                            'o_id' => intval($_REQUEST['o_id']),
                            't_id' => $info['t_id'],
                            'ol_remark' => 'paybytype:' . $type,
                            'ol_manager' => self::$manager_status,
                            'ol_in_time' => time(),
                        );
                        $log_dao->addData($log_data);
                        echo json_encode(0);exit;
                    }
                }
                $this->db->rollback();
            }
        }
        echo json_encode(1);exit;
    }

    //public function del()
    //{
    //    $result = 0;
    //    if (isset($_REQUEST['o_id']))
    //    {
    //        if (is_array($_REQUEST['o_id']) || strpos($_REQUEST['o_id'], ','))
    //        {
    //            $result = $this->orders_dao->delData(array('o_id' => array('type' => 'in', 'value' => $_REQUEST['o_id']))); //伪删除
    //        }
    //        else
    //        {
    //            $result = $this->orders_dao->delData(intval($_REQUEST['o_id'])); //伪删除
    //        }
    //    }
    //    if (!$result) {
    //        //FAILED
    //        msg('操作失败,不允许删除', 0);
    //    }
    //    //SUCCESSFUL
    //    msg('操作成功', 1, '/Msg/list');
    //}
    //
    //public function info()
    //{
    //    $info = array();
    //    if (isset($_REQUEST['o_id']) || isset($_REQUEST['key']))
    //    {
    //        if (isset($_REQUEST['o_id']))
    //        {
    //            $info = $this->orders_dao->infoData(intval($_REQUEST['o_id']));
    //        }
    //        elseif (isset($_REQUEST['key']))
    //        {
    //            $info = $this->orders_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
    //        }
    //    }
    //    $this->tpl->assign('info', $info);
    //    $this->mydisplay();
    //}

    //public function list()
    //{
    //    $list = $data = array();
    //    if (isset($_REQUEST['o_id'])) $data['o_id'] = array('type' => 'in', value => $_REQUEST['o_id']);
    //    if (isset($_REQUEST['t_id'])) $data['t_id'] = intval($_REQUEST['t_id']);
    //    if (isset($_REQUEST['u_id'])) $data['u_id'] = intval($_REQUEST['u_id']);
    //    if (isset($_REQUEST['o_worker'])) $data['o_worker'] = intval($_REQUEST['o_worker']);
    //    if (isset($_REQUEST['o_amount'])) $data['o_amount'] = intval($_REQUEST['o_amount']);
    //    if (isset($_REQUEST['o_status'])) $data['o_status'] = intval($_REQUEST['o_status']);
    //    if (isset($_REQUEST['tew_id'])) $data['tew_id'] = intval($_REQUEST['tew_id']);
    //
    //    if (isset($_REQUEST['o_start_time'])) $data['o_in_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['o_start_time']));
    //    if (isset($_REQUEST['o_end_time'])) $data['o_in_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['o_end_time']));
    //    if (isset($_REQUEST['o_start_time']) && isset($_REQUEST['o_end_time']) && $_REQUEST['o_start_time'] != 0 && $_REQUEST['o_end_time'] != 0 && strtotime($_REQUEST['o_end_time']) < strtotime($_REQUEST['o_start_time']))
    //    {
    //        //结束时间不能小于开始时间
    //        msg('结束时间不能小于开始时间', 0);
    //    }
    //    $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    //
    //    $list = $this->orders_dao->listData($data);
    //    $this->tpl->assign('list', $list);
    //    $this->myPager($list['pager']);
    //    $this->mydisplay();
    //}

}