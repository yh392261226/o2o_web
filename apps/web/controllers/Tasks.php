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
    {
        $list = $data = array();
        $data['o_worker'] = intval($_REQUEST['o_worker']);
        if ($data['o_worker'] > 0)
        {
            $this->orders_dao = new \WDAO\Orders();
            if (isset($_REQUEST['o_id'])) $data['o_id'] = array('type' => 'in', 'value' => $_REQUEST['o_id']);
            if (isset($_REQUEST['t_id'])) $data['t_id'] = intval($_REQUEST['t_id']);
            if (isset($_REQUEST['u_id'])) $data['u_id'] = intval($_REQUEST['u_id']);

            if (isset($_REQUEST['o_status'])) $data['o_status'] = intval($_REQUEST['o_status']);
            if (isset($_REQUEST['s_id'])) $data['s_id'] = intval($_REQUEST['s_id']);
            if (isset($_REQUEST['tew_id'])) $data['tew_id'] = intval($_REQUEST['tew_id']);
            //区间值
            if (isset($_REQUEST['ge_amount']) && floatval($_REQUEST['ge_amount']) > 0) $data['o_amount'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['ge_amount']));
            if (isset($_REQUEST['le_amount']) && floatval($_REQUEST['le_amount']) > 0) $data['o_amount'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['le_amount']));
            if (isset($_REQUEST['ge_in_time']) && intval($_REQUEST['ge_in_time']) > 0) $data['o_in_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['ge_in_time']));
            if (isset($_REQUEST['le_in_time']) && intval($_REQUEST['le_in_time']) > 0) $data['o_in_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['le_in_time']));
            if (isset($_REQUEST['ge_in_time']) && intval($_REQUEST['ge_in_time']) > 0) $data['o_last_edit_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['ge_in_time']));
            if (isset($_REQUEST['le_in_time']) && intval($_REQUEST['le_in_time']) > 0) $data['o_last_edit_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['le_in_time']));

            $data['leftjoin'] = array('tasks', ' orders.t_id = tasks.t_id ');
            $data['fields'] = 'orders.o_id, orders.t_id, orders.u_id, orders.o_worker, orders.o_amount, orders.o_in_time, orders.o_last_edit_time, orders.o_status, orders.tew_id, orders.s_id,
            tasks.t_id, tasks.t_title, tasks.t_status, tasks.t_author, tasks.t_phone, tasks.t_phone_status, tasks.t_amount, tasks.t_edit_amount, tasks.t_duration, tasks.t_amount_edit_times, tasks.t_posit_x, tasks.t_posit_y, tasks.t_in_time';
            //$data['where'] = ' orders.o_worker = "' . intval($_REQUEST['o_worker']) . '"';
            $data['pager'] = 0;
            $data['order'] = 'orders.o_id desc';
            $list = $this->orders_dao->listData($data);
        }
        if (!empty($list))
        {
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
        $data['t_storage'] = 0;
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

        if (isset($_REQUEST['skills']) && intval($_REQUEST['skills']) > 0) $data['leftjoin'] = array('task_ext_worker', ' task_ext_worker.t_id = tasks.t_id and task_ext_worker.tew_skills = "' . intval($_REQUEST['skills']) . '"');
        if (isset($data['leftjoin']))
        {
            $data['fields'] = 'tasks.t_id, tasks.t_title, tasks.t_status, tasks.t_author, tasks.t_phone, tasks.t_phone_status, tasks.t_amount, tasks.t_edit_amount, tasks.t_duration, tasks.t_amount_edit_times, tasks.t_posit_x, tasks.t_posit_y, tasks.t_in_time, tasks.t_storage, task_ext_worker.tew_skills, task_ext_worker.tew_worker_num, task_ext_worker.tew_price, task_ext_worker.tew_start_time, task_ext_worker.tew_end_time, task_ext_worker.r_province, task_ext_worker.r_city, task_ext_worker.r_area, task_ext_worker.tew_address, task_ext_worker.tew_lock';
        }
        $data['pager'] = 0;
        $data['order'] = 't_id desc';
        $list = $this->tasks_dao->listData($data);

        if (!empty($list))
        {
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
            $info['t_workers'] = $info['t_desc'] = $desc = $workers = array();
            $desc = $this->task_ext_info_dao->infoData(intval($_REQUEST['t_id']));
            if (!empty($desc))
            {
                $info['t_desc'] = $desc['t_desc'];
            }
            unset($desc);
            $workers = $this->task_ext_worker_dao->listData(array('pager' => 0, 't_id' => intval($_REQUEST['t_id'])));
            if (!empty($workers['data']))
            {
                $info['t_workers'] = $workers['data'];
            }
            unset($workers);
            $this->exportData($info);
        }
        else
        {
            $this->exportData();
        }
    }

    /**
     * 任务添加
     */
    public function add()
    {
        $data = $message = $tmp = array();
        if (isset($_REQUEST['basic'][0]['data']) && '' != trim($_REQUEST['basic'][0]['data'])) $data['t_title'] = trim($_REQUEST['basic'][0]['data']);
        if (!isset($data['t_title'])) $message[] = '标题不能为空';
        if (isset($_REQUEST['basic'][1]['data']) && '' != trim($_REQUEST['basic'][1]['data'])) $data['t_info'] = $data['t_desc'] = trim($_REQUEST['basic'][1]['data']);
        if (!isset($data['t_info'])) $message[] = '简介不能为空';
        if (isset($_REQUEST['t_amount']) && 0 < floatval($_REQUEST['t_amount'])) $data['t_amount'] = $data['t_edit_amount'] = floatval($_REQUEST['t_amount']);
        if (!isset($data['t_amount'])) $message[] = '任务总价不能小于等于0';
        if (isset($_REQUEST['t_duration']) && 1 <= intval($_REQUEST['t_duration'])) $data['t_duration'] = intval($_REQUEST['t_duration']);
        if (!isset($data['t_duration'])) $message[] = '任务时长不能小于1天';
        if (isset($_REQUEST['t_posit_x']) && is_float($_REQUEST['t_posit_x'])) $data['t_posit_x'] = floatval($_REQUEST['t_posit_x']);
        if (!isset($data['t_posit_x'])) $message[] = 'x轴坐标不正确';
        if (isset($_REQUEST['t_posit_y']) && is_float($_REQUEST['t_posit_y'])) $data['t_posit_y'] = floatval($_REQUEST['t_posit_y']);
        if (!isset($data['t_posit_y'])) $message[] = 'y轴坐标不正确';
        if (isset($_REQUEST['t_author']) && 0 < intval($_REQUEST['t_author'])) $data['t_author'] = $data['t_last_editor'] = intval($_REQUEST['t_author']);
        if (!isset($data['t_author'])) $message[] = '发布人id不正确';
        if (isset($_REQUEST['t_phone']) && '' != trim($_REQUEST['t_phone'])) $data['t_phone'] = trim($_REQUEST['t_phone']);
        if (isset($_REQUEST['t_type']) && '' != trim($_REQUEST['t_type'])) $data['t_type'] = trim($_REQUEST['t_type']);
        $data['t_phone_status'] = 1;
        if (isset($_REQUEST['t_phone_status']) && in_array($_REQUEST['t_phone_status'], array(0, 1))) $data['t_phone_status'] = intval($_REQUEST['t_phone_status']);
        $data['t_in_time'] = $data['t_last_edit_time'] = time();
        $data['t_amount_edit_times'] = 0;
        $data['t_status'] = 0;
        if (isset($_REQUEST['t_status']) && is_int($_REQUEST['t_status'])) $data['t_status'] = intval($_REQUEST['t_status']);
        $data['t_storage'] = 0;
        if (isset($_REQUEST['t_storage']) && is_int($_REQUEST['t_storage'])) $data['t_storage'] = intval($_REQUEST['t_storage']);

        if (isset($_REQUEST['province']) && intval($_REQUEST['province']) > 0) $tmp['province'] = intval($_REQUEST['province']);
        if (isset($_REQUEST['city']) && intval($_REQUEST['city']) > 0) $tmp['city'] = intval($_REQUEST['city']);
        if (isset($_REQUEST['area']) && intval($_REQUEST['area']) > 0) $tmp['area'] = intval($_REQUEST['area']);
        if (isset($_REQUEST['address']) && '' != trim($_REQUEST['address'])) $tmp['address'] = trim($_REQUEST['address']);

        /*tasks_ext_info*/
        if (isset($_REQUEST['t_desc']) && '' != trim($_REQUEST['t_desc'])) $data['t_desc'] = trim($_REQUEST['t_desc']);

        /*tasks_ext_worker*/
        if (isset($_REQUEST['worker']) && !empty($_REQUEST['worker']))
        {
            if (!empty($_REQUEST['worker']))
            {
                $worker = array();
                foreach ($_REQUEST['worker'] as $key => $val)
                {
                    $worker[$key]['tew_skills'] = $val['skill'];
                    $worker[$key]['tew_worker_num'] = $val['personNum'];
                    $worker[$key]['tew_price'] = $val['money'];
                    $worker[$key]['tew_start_time'] = $val['startTime'];
                    $worker[$key]['tew_end_time'] = $val['endTime'];
                    $worker[$key]['r_province'] = isset($tmp['province']) ? $tmp['province'] : 0;
                    $worker[$key]['r_city'] = isset($tmp['city']) ? $tmp['city'] : 0;
                    $worker[$key]['r_area'] = isset($tmp['area']) ? $tmp['area'] : 0;
                    $worker[$key]['tew_address'] = isset($tmp['address']) ? $tmp['address'] : '';
                    $worker[$key]['tew_lock'] = 0;
                }
            }
        }


    }

}