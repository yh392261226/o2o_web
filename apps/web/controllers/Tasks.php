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
        $this->task_ext_info_dao = new \WDAO\Task_ext_info();
        $this->task_ext_worker_dao = new \WDAO\Task_ext_worker();
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
            $data['fields'] = 'tasks.t_id, tasks.t_title, tasks.t_status, tasks.t_author, tasks.t_phone, tasks.t_phone_status, tasks.t_amount, tasks.t_edit_amount, tasks.t_duration, tasks.t_amount_edit_times, tasks.t_posit_x, tasks.t_posit_y, tasks.t_in_time, task_ext_worker.tew_skills, task_ext_worker.tew_worker_num, task_ext_worker.tew_price, task_ext_worker.tew_start_time, task_ext_worker.tew_end_time, task_ext_worker.r_province, task_ext_worker.r_city, task_ext_worker.r_area, task_ext_worker.tew_address, task_ext_worker.tew_lock';
        }
        $data['pager'] = 0;
        $data['order'] = 't_id desc';
        $list = $this->tasks_dao->listData($data);

        if (!empty($list))
        {
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

}