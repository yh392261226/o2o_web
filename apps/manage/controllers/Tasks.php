<?php
namespace App\Controller;

class Tasks extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->tasks_dao = new \MDAO\Tasks();
        $this->task_ext_info_dao = new \MDAO\Task_ext_info();
        $this->task_ext_worker_dao = new \MDAO\Task_ext_worker();
        $this->orders_dao = new \MDAO\Orders();
        //$this->db->debug = 1;
    }

    /**
     * ****[ tasks ]***********************************************************************************************
     */
    public function info()
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
            $desc = $this->task_ext_info_dao->infoData(intval($_REQUEST['t_id']));
            if (!empty($desc))
            {
                $info['t_desc'] = $desc['t_desc'];
            }
            else
            {
                $info['t_desc'] = '';
            }
            unset($desc);

            $workers = $this->task_ext_worker_dao->listData(
                array(
                    'fields' => 'task_ext_worker.*, skills.s_name',
                    't_id' => intval($_REQUEST['t_id']
                    ),
                    'pager' => 0,
                    'join' => array(
                        'skills',
                        'task_ext_worker.tew_skills = skills.s_id',
                    )
                ));
            if (!empty($workers['data']))
            {
                $info['workers'] = $workers['data'];
            }
            else
            {
                $info['workers'] = array();
            }
            unset($workers);
        }

//print_r($info);

        $data = array(
            'pager' => 0,
            't_id' => intval($_REQUEST['t_id']),
        );
        $orders_list = $this->orders_dao->listData($data);

        // $s_id_key = array();
        // $newlist = array();
        // foreach ($orders_list['data'] as $key => $value) {
        //     if(isset($value['s_id'])){
        //         if(in_array($value['s_id'],$s_id_key)){
        //             $newlist[$value['s_id']][] = $value;
        //         }else{
        //             $s_id_key[] = $value['s_id'];
        //             $newlist[$value['s_id']][] = $value;
        //         }
        //     }
        // }
        // var_dump($orders_list);die;
        //print_r($orders_list);
        $this->tpl->assign('info', $info);
        $this->tpl->assign('orders_list', $orders_list);
        $this->mydisplay();
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['t_id'])) $data['t_id'] = array('type' => 'in', value => $_REQUEST['t_id']);
        if (isset($_REQUEST['t_title'])) $data['t_title'] = array('type'=>'like', 'value' => trim($_REQUEST['t_title']));
        if (isset($_REQUEST['t_duration'])) $data['t_duration'] = intval($_REQUEST['t_duration']);
        if (isset($_REQUEST['t_posit_x'])) $data['t_posit_x'] = intval($_REQUEST['t_posit_x']);
        if (isset($_REQUEST['t_posit_y'])) $data['t_posit_y'] = intval($_REQUEST['t_posit_y']);
        if (isset($_REQUEST['t_author'])) $data['t_author'] = intval($_REQUEST['t_author']);
        if (isset($_REQUEST['t_status'])) $data['t_status'] = intval($_REQUEST['t_status']);
        if (isset($_REQUEST['t_phone_status'])) $data['t_phone_status'] = intval($_REQUEST['t_phone_status']);
        if (isset($_REQUEST['t_phone'])) $data['t_phone'] = trim($_REQUEST['t_phone']);
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $data['t_storage'] = 0;

        //可以区间值
        if (isset($_REQUEST['t_in_time'])) $data['t_in_time'] = strtotime(trim($_REQUEST['t_in_time']));
        if (isset($_REQUEST['t_last_edit_time'])) $data['t_last_edit_time'] = strtotime(trim($_REQUEST['t_last_edit_time']));
        if (isset($_REQUEST['t_amount'])) $data['t_amount'] = intval($_REQUEST['t_amount']);
        if (isset($_REQUEST['t_edit_amount'])) $data['t_edit_amount'] = intval($_REQUEST['t_edit_amount']);
        if (isset($_REQUEST['t_amount_edit_times'])) $data['t_amount_edit_times'] = intval($_REQUEST['t_amount_edit_times']);

        $list = $this->tasks_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

}