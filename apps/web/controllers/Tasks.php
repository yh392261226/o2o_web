<?php
/**
 * 工种接口
 */
namespace App\Controller;

class Tasks extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->tasks_dao = new \WDAO\Tasks();
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

    private function worked()
    {$this->db->debug = 1;
        $list = $data = array();
        $data['o_worker'] = intval($_REQUEST['o_worker']);
        if ($data['o_worker'] > 0)
        {
            $this->orders_dao = new \WDAO\Orders();
            if (isset($_REQUEST['o_id'])) $data['o_id'] = array('type' => 'in', 'value' => $_REQUEST['o_id']);
            $data['where'] = '1';
            if (isset($_REQUEST['t_id'])) $data['where'] .= ' and orders.t_id = ' . intval($_REQUEST['t_id']);
            if (isset($_REQUEST['u_id'])) $data['where'] .= ' and orders.u_id = ' . intval($_REQUEST['u_id']);

            if (isset($_REQUEST['o_status']) && is_numeric($_REQUEST['o_status'])) $data['where'] .= 'and orders.o_status = ' . intval($_REQUEST['o_status']);
            if (isset($_REQUEST['o_confirm'])) $data['where'] .= ' and orders.o_confirm in (' . trim($_REQUEST['o_confirm'] . ')');
            if (isset($_REQUEST['s_id'])) $data['where'] .= ' and orders.s_id = ' . intval($_REQUEST['s_id']);
            if (isset($_REQUEST['tew_id'])) $data['where'] .= ' and orders.tew_id = ' . intval($_REQUEST['tew_id']);
            //区间值
            if (isset($_REQUEST['ge_amount']) && floatval($_REQUEST['ge_amount']) > 0) $data['o_amount'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_amount']));
            if (isset($_REQUEST['le_amount']) && floatval($_REQUEST['le_amount']) > 0) $data['o_amount'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_amount']));
            if (isset($_REQUEST['ge_in_time']) && intval($_REQUEST['ge_in_time']) > 0) $data['o_in_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['ge_in_time']));
            if (isset($_REQUEST['le_in_time']) && intval($_REQUEST['le_in_time']) > 0) $data['o_in_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['le_in_time']));
            if (isset($_REQUEST['ge_in_time']) && intval($_REQUEST['ge_in_time']) > 0) $data['o_last_edit_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['ge_in_time']));
            if (isset($_REQUEST['le_in_time']) && intval($_REQUEST['le_in_time']) > 0) $data['o_last_edit_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['le_in_time']));

            $data['join'] = array('tasks', ' orders.t_id = tasks.t_id ');
            $data['walk']['_join'] = array('join' => array('task_ext_worker', 'orders.tew_id = task_ext_worker.tew_id'));
            $data['fields'] = 'orders.o_id, orders.t_id, orders.u_id, orders.o_worker, orders.o_amount, orders.o_in_time, orders.o_last_edit_time, orders.o_status, orders.tew_id, orders.s_id, orders.o_confirm, orders.unbind_time,
            tasks.t_id, tasks.t_title, tasks.t_info, tasks.t_status, tasks.t_author, tasks.t_phone, tasks.t_phone_status, tasks.t_amount, tasks.t_edit_amount, tasks.t_duration, tasks.t_amount_edit_times, tasks.t_posit_x, tasks.t_posit_y, tasks.t_in_time,
            task_ext_worker.tew_skills, task_ext_worker.tew_worker_num, task_ext_worker.tew_price, task_ext_worker.tew_start_time, task_ext_worker.tew_end_time, task_ext_worker.r_province, task_ext_worker.r_city, task_ext_worker.r_area, task_ext_worker.tew_address';
            //$data['where'] = ' orders.o_worker = "' . intval($_REQUEST['o_worker']) . '"';
            $data['pager'] = 0;
            $data['order'] = 'orders.o_in_time, orders.o_id desc';
            $list = $this->orders_dao->listData($data);
        }
        if (!empty($list['data']))
        {
            foreach ($list['data'] as $key => $val)
            {
                $list['data'][$key]['u_img'] = $this->getHeadById($val['t_author']);
            }
            $this->exportData($list['data']);
        }
        else
        {
            $this->exportData();
        }
    }

    //列表及搜索
    private function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['t_id'])) $data['t_id'] = array('type' => 'in', 'value' => $_REQUEST['t_id']);
        if (isset($_REQUEST['t_title'])) $data['t_title'] = array('type'=>'like', 'value' => trim($_REQUEST['t_title']));
        if (isset($_REQUEST['t_status'])) $data['t_status'] = intval($_REQUEST['t_status']);
        if (isset($_REQUEST['t_author'])) $data['t_author'] = intval($_REQUEST['t_author']);
        if (isset($_REQUEST['t_phone'])) $data['t_phone'] = intval($_REQUEST['t_phone']);
        if (isset($_REQUEST['t_phone_status'])) $data['t_phone_status'] = intval($_REQUEST['t_phone_status']);
        $data['where'] = 't_storage = 0';
        if (isset($_REQUEST['t_storage'])) $data['t_storage'] = intval($_REQUEST['t_storage']);

        //price between
        if (isset($_REQUEST['ge_amount']) && floatval($_REQUEST['ge_amount']) > 0) $data['t_amount'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_amount']));
        if (isset($_REQUEST['le_amount']) && floatval($_REQUEST['le_amount']) > 0) $data['t_amount'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_amount']));
        if (isset($_REQUEST['ge_edit_amount']) && floatval($_REQUEST['ge_edit_amount']) > 0) $data['t_edit_amount'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_edit_amount']));
        if (isset($_REQUEST['le_edit_amount']) && floatval($_REQUEST['le_edit_amount']) > 0) $data['t_edit_amount'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_edit_amount']));
        //times&days between
        if (isset($_REQUEST['ge_duration']) && floatval($_REQUEST['ge_duration']) > 0) $data['t_duration'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_duration']));
        if (isset($_REQUEST['le_duration']) && floatval($_REQUEST['le_duration']) > 0) $data['t_duration'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_duration']));
        if (isset($_REQUEST['ge_amount_edit_times'])) $data['t_amount_edit_times'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['ge_amount_edit_times']));
        if (isset($_REQUEST['le_amount_edit_times'])) $data['t_amount_edit_times'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['le_amount_edit_times']));
        //position between
        if (isset($_REQUEST['ge_posit_x'])) $data['t_posit_x'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_posit_x']));
        if (isset($_REQUEST['le_posit_x'])) $data['t_posit_x'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_posit_x']));
        if (isset($_REQUEST['ge_posit_y'])) $data['t_posit_y'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_posit_y']));
        if (isset($_REQUEST['le_posit_y'])) $data['t_posit_y'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_posit_y']));
        //time between
        if (isset($_REQUEST['ge_in_time']) && intval($_REQUEST['ge_in_time']) > 0) $data['t_in_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['ge_in_time']));
        if (isset($_REQUEST['le_in_time']) && intval($_REQUEST['le_in_time']) > 0) $data['t_in_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['le_in_time']));

        if (isset($_REQUEST['skills']) && intval($_REQUEST['skills']) > 0)
        {
            $data['leftjoin'] = array('task_ext_worker', ' task_ext_worker.t_id = tasks.t_id');
            $data['where'] .=  ' and task_ext_worker.tew_skills in (' . trim($_REQUEST['skills']) . ')';
            $data['fields'] = 'tasks.t_id, tasks.t_title, tasks.t_status, tasks.t_author, tasks.t_phone, tasks.t_phone_status, tasks.t_amount, tasks.t_edit_amount, tasks.t_duration, tasks.t_amount_edit_times, tasks.t_posit_x, tasks.t_posit_y, tasks.t_in_time, tasks.t_storage, task_ext_worker.tew_id, task_ext_worker.tew_skills, task_ext_worker.tew_worker_num, task_ext_worker.tew_price, task_ext_worker.tew_start_time, task_ext_worker.tew_end_time, task_ext_worker.r_province, task_ext_worker.r_city, task_ext_worker.r_area, task_ext_worker.tew_address, task_ext_worker.tew_lock';
        }
        $data['pager'] = 0;
        $data['order'] = 'tasks.t_id desc';
        $list = $this->tasks_dao->listData($data);

        if (!empty($list))
        {
            $tasks_ids = array();
            foreach ($list['data'] as $key => $val)
            {
                $list['data'][$key]['workers'] = array();
                $list['data'][$key]['favorate'] = 0;
                $list['data'][$key]['u_img'] = $this->getHeadById($val['t_author']);
                $tasks_ids[] = isset($val['t_id']) && $val['t_id'] > 0 ? $val['t_id'] : 0;
            }
            unset($key, $val);
            if (!empty($tasks_ids) && !isset($data['leftjoin']))
            {
                $tasks_ext_worker_dao = new \WDAO\Task_ext_worker();
                $tasks_ext_worker_data = $tasks_ext_worker_dao->listData(array('t_id' => array('type' => 'in', 'value' => $tasks_ids), 'pager' => 0));
                if (!empty($tasks_ext_worker_data['data']))
                {
                    foreach ($list['data'] as $k => $v)
                    {
                        foreach ($tasks_ext_worker_data['data'] as $key => $val)
                        {
                            if ($v['t_id'] == $val['t_id'])
                            {
                                $list['data'][$k]['workers'][] = $val;
                            }
                        }
                    }
                    unset($key, $val, $k, $v, $tasks_ext_worker_dao, $tasks_ext_worker_data);
                }
            }

            if (isset($_REQUEST['u_id']) && intval($_REQUEST['u_id']) > 0)
            {

                $this->users_favorate_dao = new \WDAO\Users_favorate(array('table' => 'Users_favorate'));
                $favorates = $marked = array();
                $favorates = $this->users_favorate_dao->listData(array('f_type' => 0, 'u_id' => intval($_REQUEST['u_id']), 'pager' => 0));
                if (!empty($favorates['data']))
                {
                    foreach ($favorates['data'] as $key => $val)
                    {
                        $marked[] = $val['f_type_id'];
                    }
                    unset($key, $val);
                    if (!empty($marked))
                    {
                        foreach ($list['data'] as $key => $val)
                        {
                            if (in_array($val['t_id'], $marked))
                            {
                                $list['data'][$key]['favorate'] = 1;
                            }
                        }
                        unset($marked, $favorates);
                    }
                }
            }
            $this->exportData($list['data']);
        }
        else
        {
            $this->exportData();
        }
    }

    //详情
    private function info()
    {
        $info = array();
        if (isset($_REQUEST['t_id']) || isset($_REQUEST['key']))
        {
            $this->task_ext_info_dao = new \WDAO\Task_ext_info();
            $this->task_ext_worker_dao = new \WDAO\Task_ext_worker();
            if (isset($_REQUEST['t_id']))
            {
                $info = $this->tasks_dao->infoData(intval($_REQUEST['t_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->tasks_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }

        if (!empty($info))
        {
            $user_dao = new \WDAO\Users(array('table' => 'users'));
            $author_info = $user_dao->infoData(array('key' => 'u_id', 'val' => $info['t_author'], 'fields' => 'u_id, u_mobile, u_sex, u_true_name'));
            if (!empty($author_info))
            {
                $info += $author_info;
            }
            unset($user_dao, $author_info);
            $info['u_img'] = $this->getHeadById($info['t_author']);
            $info['r_province'] = 0;
            $info['r_city'] = 0;
            $info['r_area'] = 0;
            $info['tew_address'] = '';

            $info['t_workers'] = $info['t_desc'] = $desc = $workers = array();
            $desc = $this->task_ext_info_dao->infoData(intval($_REQUEST['t_id']));
            if (!empty($desc))
            {
                $info['t_desc'] = $desc['t_desc'];
            }
            unset($desc);
            $workers_param = array(
                'pager' => 0,
                'where' => 'task_ext_worker.t_id =' . intval($_REQUEST['t_id']),
                );
            $workers = $this->task_ext_worker_dao->listData($workers_param);

            if (!empty($workers['data']))
            {
                $orders_param = $tew_ids = array();
                foreach ($workers['data'] as $key => $val)
                {
                    $workers['data'][$key]['remaining'] = $val['tew_worker_num'];
                    $tew_ids[] = isset($val['tew_id']) && $val['tew_id'] > 0 ? $val['tew_id'] : 0;
                    if ($key == 0)
                    {
                        $info['r_province'] = $val['r_province'];
                        $info['r_city'] = $val['r_city'];
                        $info['r_area'] = $val['r_area'];
                        $info['tew_address'] = $val['tew_address'];
                    }
                }
                unset($key, $val);
                if (!empty($tew_ids))
                {
                    $orders_param['tew_id'] = array('type' => 'in', 'value' => $tew_ids);
                    $orders_param['pager'] = 0;
                    $orders_param['o_confirm'] = 1;
                    $orders_dao = new \WDAO\Orders();
                    $orders_data = $orders_dao->listData($orders_param);
                    if (!empty($orders_data['data']))
                    {
                        foreach ($workers['data'] as $key => $val)
                        {
                            $order_count = 0;
                            foreach ($orders_data['data'] as $k => $v)
                            {
                                if ($val['tew_id'] == $v['tew_id'])
                                {
                                    $order_count += 1;
                                    $workers['data'][$key]['orders'][] = $v;
                                }
                            }
                            $workers['data'][$key]['remaining'] = $val['tew_worker_num'] - $order_count;
                            unset($k, $v);
                        }
                        unset($key, $val, $order_count, $orders_data);
                    }
                }

                if (!empty($workers['data']))
                {
                    $info['t_workers'] = $workers['data'];
                }
                unset($workers);
            }
            $this->exportData($info);
        }
        else
        {
            $this->exportData();
        }
    }

    /**
     * 任务发布/草稿
     */
    private function publish()
    {
        //$this->db->debug = 1;
        $data = $info = $worker = $fields = $message = $tmp = array();
        echo json_encode($_REQUEST);exit;
        error_log($_REQUEST['basic'], 3, './log.log');
        if (!empty($_REQUEST['basic']))  $_REQUEST['basic'] = json_decode($_REQUEST['basic'], true);
        $data['t_storage'] = $tmp['t_storage'] = 1;
        if (isset($_REQUEST['basic']['t_storage']) && is_numeric($_REQUEST['basic']['t_storage'])) $data['t_storage'] = intval($_REQUEST['basic']['t_storage']);
        $data['t_title'] = '未命名任务';
        if (isset($_REQUEST['basic'][0][0]['data']) && '' != trim($_REQUEST['basic'][0][0]['data'])) $data['t_title'] = trim($_REQUEST['basic'][0][0]['data']);
        if (isset($_REQUEST['basic'][0][1]['data']) && '' != trim($_REQUEST['basic'][0][1]['data'])) $data['t_info'] = trim($_REQUEST['basic'][0][1]['data']);
        if (isset($_REQUEST['basic']['t_amount']) && 0 < floatval($_REQUEST['basic']['t_amount'])) $data['t_amount'] = $data['t_edit_amount'] = floatval($_REQUEST['basic']['t_amount']);
        if (!isset($_REQUEST['basic']['t_duration'])) $_REQUEST['basic']['t_duration'] = 1;
        if (isset($_REQUEST['basic']['t_duration']) && 1 <= intval($_REQUEST['basic']['t_duration'])) $data['t_duration'] = intval($_REQUEST['basic']['t_duration']);
        if (isset($_REQUEST['basic']['t_posit_x'])) $data['t_posit_x'] = floatval($_REQUEST['basic']['t_posit_x']);
        if (isset($_REQUEST['basic']['t_posit_y'])) $data['t_posit_y'] = floatval($_REQUEST['basic']['t_posit_y']);
        if (isset($_REQUEST['basic'][5]['t_author']) && 0 < intval($_REQUEST['basic'][5]['t_author'])) $data['t_author'] = $data['t_last_editor'] = intval($_REQUEST['basic'][5]['t_author']);
        if ($data['t_storage'] == 0)
        {
            if (!isset($data['t_title'])) $message[] = '标题不能为空';
            if (!isset($data['t_info'])) $message[] = '简介不能为空';
            //if (!isset($data['t_duration'])) $message[] = '任务时长不能小于1天';
            if (!isset($data['t_posit_x'])) $message[] = 'x轴坐标不正确';
            if (!isset($data['t_posit_y'])) $message[] = 'y轴坐标不正确';
        }
        if (!isset($data['t_author'])) $message[] = '发布人id不正确';
        if (!empty($message))
        {
            $this->exportData($message);
        }

        //if (isset($_REQUEST['t_posit_y'])) $data['t_posit_y'] = floatval($_REQUEST['t_posit_y']);
        $data['t_type'] = 0;
        if (isset($_REQUEST[0][2]['data']) && is_numeric($_REQUEST[0][2]['data'])) $data['t_type'] = intval($_REQUEST[0][2]['data']);
        $data['t_phone_status'] = 1;
        if (isset($_REQUEST['t_phone_status']) && in_array($_REQUEST['t_phone_status'], array(0, 1))) $data['t_phone_status'] = intval($_REQUEST['t_phone_status']);
        $data['t_in_time'] = $data['t_last_edit_time'] = time();
        $data['t_amount_edit_times'] = 0;
        $data['t_status'] = 0;
        if (isset($_REQUEST['basic']['t_status']) && is_numeric($_REQUEST['basic']['t_status'])) $data['t_status'] = intval($_REQUEST['basic']['t_status']);
        if (isset($_REQUEST['basic']['province']) && intval($_REQUEST['basic']['province']) > 0) $tmp['province'] = intval($_REQUEST['basic']['province']);
        if (isset($_REQUEST['basic']['city']) && intval($_REQUEST['basic']['city']) > 0) $tmp['city'] = intval($_REQUEST['basic']['city']);
        if (isset($_REQUEST['basic']['area']) && intval($_REQUEST['basic']['area']) > 0) $tmp['area'] = intval($_REQUEST['basic']['area']);
        if (isset($_REQUEST[0][4]['data']) && '' != trim($_REQUEST[0][4]['data'])) $tmp['address'] = trim($_REQUEST[0][4]['data']);
        if (isset($_REQUEST['basic']['t_id']) && intval($_REQUEST['basic']['t_id']) > 0) $tmp['id'] = intval($_REQUEST['basic']['t_id']); //任务id
        if (isset($_REQUEST['basic']['u_pass']) && '' != trim($_REQUEST['basic']['u_pass'])) $tmp['u_pass'] = trim($_REQUEST['basic']['u_pass']); //任务id

        //写入任务
        $task_dao = new \WDAO\Tasks();
        $result = $task_dao->addData($data);
        if (!$result)
        {
            $this->exportData('failure');
        }

        /*tasks_ext_info*/
        $tmp['t_id'] = $info['t_id'] = $result;
        $info['t_desc'] = (isset($data['t_info']) && '' != trim($data['t_info'])) ? trim($data['t_info']) : '';
        if (isset($_REQUEST['basic']['t_desc']) && '' != trim($_REQUEST['basic']['t_desc'])) $info['t_desc'] = trim($_REQUEST['basic']['t_desc']);
        $ext_info_dao = new \WDAO\Task_ext_info();
        $info_result = $ext_info_dao->addData($info);
        if (!$info_result)
        {
            $tmp['t_storage'] = 0; //如果插入失败立马标注进草稿箱
        }

        /*tasks_ext_worker*/
        $tmp['total'] = $tmp['total_edit'] = 0;
        if (isset($_REQUEST['worker']) && !empty($_REQUEST['worker']))
        {
            $fields = array('t_id', 'tew_skills', 'tew_worker_num', 'tew_price', 'tew_start_time', 'tew_end_time', 'r_province', 'r_city', 'r_area', 'tew_address', 'tew_lock');
            foreach ($_REQUEST['worker'] as $key => $val)
            {
                $worker[$key][] = $tmp['t_id'];
                $worker[$key][] = isset($val[0]['skill']) ? intval($val[0]['skill']) : 0;
                $worker[$key][] = isset($val[1]['personNum']) ? intval($val[1]['personNum']) : 0;
                $worker[$key][] = isset($val[1]['money']) ? floatval($val[1]['money']) : 0;
                $worker[$key][] = isset($val[2]['startTime']) ? strtotime($val[2]['startTime']) : 0;
                $worker[$key][] = isset($val[2]['endTime']) ? strtotime($val[2]['endTime']) : 0;
                $worker[$key][] = isset($tmp['province']) ? $tmp['province'] : 0;
                $worker[$key][] = isset($tmp['city']) ? $tmp['city'] : 0;
                $worker[$key][] = isset($tmp['area']) ? $tmp['area'] : 0;
                $worker[$key][] = isset($tmp['address']) ? $tmp['address'] : '';
                $worker[$key][] = 0;
                $tmp['total'] = $tmp['total_edit'] += $worker[$key][2] * $worker[$key][3] * (ceil($worker[$key][6] - $worker[$key][5]) / 3600 / 24 + 1);
            }
        }
        else
        { //预防机器人写入
            $worker['t_id'] = $tmp['t_id'];
            $worker['tew_skills'] = $worker['tew_worker_num'] = $worker['tew_price'] = $worker['tew_start_time'] = $worker['tew_end_time'] = $worker['tew_lock'] = 0;
            $worker['r_province'] = isset($tmp['province']) ? $tmp['province'] : 0;
            $worker['r_city'] = isset($tmp['city']) ? $tmp['city'] : 0;
            $worker['r_area'] = isset($tmp['area']) ? $tmp['area'] : 0;
            $worker['tew_address'] = isset($tmp['address']) ? $tmp['address'] : '';
            $tmp['total'] = $tmp['total_edit'] += $worker['tew_worker_num'] * $worker['tew_price'] * 1;
        }

        $ext_worker_dao = new \WDAO\Task_ext_worker();
        $worker_result = $ext_worker_dao->addData($worker, $fields);
        if (!$worker_result)
        {
            $tmp['t_storage'] = 0; //插入失败 立马标注进草稿箱
        }

        //删除之前的该任务 并重新写入
        if (isset($tmp['id']) && isset($data['t_author']))
        {
            $del_result = $this->_delTaks(array(
                't_id' => $tmp['id'],
                't_author' => $data['t_author'],
            ));
            if ($del_result < 0)
            {
                if ($del_result == -1) $this->exportData('无法完成任务覆盖或任务草稿不存在');
                if ($del_result == -2) $this->exportData('返还用户资金失败，请联系客服人员');
                if ($del_result == -3) $this->exportData('还原抵扣券失败，请联系客服人员');
                if ($del_result == -9) $this->exportData('参数不正确');
            }
        }

        //获取用户支付密码及
        if (!isset($tmp['u_pass']))
        {
            $this->exportData('用户支付密码不能为空');
        }
        $user_dao = new \WDAO\Users(array('table' => 'users'));
        $pass_result = $user_dao->checkUserPayPassword(array('u_id' => intval($_REQUEST['t_author']), 'u_pass' => $tmp['u_pass']));
        if (!isset($pass_result['u_mobile']) || '' == $pass_result['u_mobile'])
        {
            $this->exportData('用户支付密码错误');
        }

        //获取用户资金
        $users_ext_funds_dao = new \WDAO\Users_ext_funds(array('table'=>'users_ext_funds'));
        $users_ext_funds_info = $users_ext_funds_dao->infoData(intval($_REQUEST['t_author']));
        if (empty($users_ext_funds_info) || !isset($users_ext_funds_info['uef_overage']) || intval($users_ext_funds_info['uef_overage']) <= 0 || ($data['t_storage'] == 1 && $users_ext_funds_info['uef_overage'] < $tmp['total_edit']))
        {
            $this->exportData('用户资金不足');
        }

        //订单改价
        if ($worker_result && $data['t_storage'] == 0 && $tmp['t_storage'] == 1)
        {
            $this->db->start();
            $amount_result = $task_dao->updateData(array('t_edit_amount' => $tmp['total_edit'], 't_amount' => $tmp['total'], 't_phone' => $pass_result['u_mobile'], 't_storage' => 0), array('t_id' => $result));
            if ($amount_result)
            {
                //扣除用户资金 并加入平台资金日志
                $user_funds_result = $this->userFunds(intval($_REQUEST['t_author']), (-1 * $tmp['total_edit']), $type = 'pubtask'); //扣除用户资金
                $platform_funds_result = $this->platformFundsLog($result, $tmp['total_edit'], 3, 'pubtask', 0);     //平台资金日志增加
            }

            //事物提交或回滚
            if ($amount_result && $bouns_data_result && $user_funds_result && $platform_funds_result)
            {
                $this->db->commit();
                $this->exportData('success');
            }
            else
            {
                $this->db->rollback();
                $this->exportData('failure');
            }
        }

        if ($tmp['t_storage'] == 1)
        {
            $this->exportData('成功加入草稿箱');
        }
        else
        {
            $this->exportData('存储出错，已存入草稿箱');
        }
    }

    /**
     * 雇主删除任务 假删除 更改任务状态为-9
     */
    private function del2()
    {
        $data = array();
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']);
        if (isset($_REQUEST['t_author']) && intval($_REQUEST['t_author']) > 0) $data['t_author'] = intval($_REQUEST['t_author']);

        if (!empty($data) && isset($data['t_id']) && isset($data['t_author']))
        {
            $result = $this->tasks_dao->updateData(array('t_status' => -9), array('t_id' => $data['t_id'], 't_author' => $data['t_author']));
            if ($result)
            {
                $this->exportData('success');
            }
        }
        $this->exportData('failure');
    }

    /**
     * 删除任务及归还资金与抵扣券 [对外]
     * @param array $data
     * @return int
     */
    private function del()
    {
        $data = array();
        $del_result = -9;
        if (isset($_REQUEST['t_id']) && intval($_REQUEST['t_id']) > 0) $data['t_id'] = intval($_REQUEST['t_id']);
        if (isset($_REQUEST['t_author']) && intval($_REQUEST['t_author']) > 0) $data['t_author'] = intval($_REQUEST['t_author']);

        if (!empty($data) && isset($data['t_id']) && isset($data['t_author']))
        {
            $del_result = $this->_delTaks(array(
                't_id' => $data['t_id'],
                't_author' => $data['t_author'],
            ));
        }

        if ($del_result < 0)
        {
            if ($del_result == -1) $this->exportData('无法完成任务覆盖或任务草稿不存在');
            if ($del_result == -2) $this->exportData('返还用户资金失败，请联系客服人员');
            if ($del_result == -4) $this->exportData('该任务不存在');
            if ($del_result == -5) $this->exportData('该任务已开工，不能删除');
            if ($del_result == -9) $this->exportData('参数不正确');
        }
        $this->exportData('success');
    }

    /**
     * 删除任务及归还资金与抵扣券 [对内]
     * @param array $data
     * @return int
     */
    private function _delTaks($data = array())
    {
        if (isset($data['t_id']) && isset($data['t_author']))
        {
            $task_dao = new \WDAO\Tasks();
            $del_info = $task_dao->infoData(intval($data['t_id']));
            if (!empty($del_info))
            {
                if (isset($del_info['t_author']) && $del_info['t_author'] == intval($data['t_author']))
                {
                    if (isset($del_info['t_status']) && $del_info['t_status'] != 0 && $del_info['t_status'] != 1)
                    {
                        return -5; //任务已开始 不能删除
                    }
                }
                else
                {
                    return -4; // 任务不归该用户 即任务不存在
                }
            }
            else
            {
                return -4; // 任务不存在
            }

            //删除之前的该任务 并重新写入
            $del_old_result = $task_dao->delOldTask(array('t_id' => $data['t_id'], 't_author' => $data['t_author']));
            if (!$del_old_result)
            {
                return -1;
                //$this->exportData('无法完成任务覆盖或任务草稿不存在');
            }

            //归还已经扣除资金
            $platform_funds_dao = new \WDAO\Platform_funds_log();
            $back_platform_funds = $platform_funds_dao->rebackFundsToUser(array(
                'pfl_type' => 3,
                'pfl_reason' => 'pubtask',
                'pfl_type_id' => intval($data['t_id']),
                'u_id' => intval($data['t_author']),
            ));
            if (!$back_platform_funds)
            {
                return -2;
                //$this->exportData('返还用户资金失败，请联系客服人员');
            }
            return 0;
        }
        return -9;
    }

}