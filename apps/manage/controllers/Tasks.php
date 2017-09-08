<?php
namespace App\Controller;

class Tasks extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->tasks_dao = new \MDAO\Tasks();
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
        print_r($info);exit;
        $this->tpl->assign('info', $info);
        $this->mydisplay('info');
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['t_id'])) $data['t_id'] = array('type' => 'in', value => $_REQUEST['t_id']);
        if (isset($_REQUEST['t_title'])) $data['t_title'] = array('type'=>'like', 'value' => trim($_REQUEST['t_title']));
        if (isset($_REQUEST['t_amount'])) $data['t_amount'] = intval($_REQUEST['t_amount']);
        if (isset($_REQUEST['t_edit_amount'])) $data['t_edit_amount'] = intval($_REQUEST['t_edit_amount']);
        if (isset($_REQUEST['t_amount_edit_times'])) $data['t_amount_edit_times'] = intval($_REQUEST['t_amount_edit_times']);
        if (isset($_REQUEST['t_duration'])) $data['t_duration'] = intval($_REQUEST['t_duration']);
        if (isset($_REQUEST['t_posit_x'])) $data['t_posit_x'] = intval($_REQUEST['t_posit_x']);
        if (isset($_REQUEST['t_posit_y'])) $data['t_posit_y'] = intval($_REQUEST['t_posit_y']);
        if (isset($_REQUEST['t_author'])) $data['t_author'] = intval($_REQUEST['t_author']);
        if (isset($_REQUEST['t_in_time'])) $data['t_in_time'] = strtotime(trim($_REQUEST['t_in_time']));
        if (isset($_REQUEST['t_last_edit_time'])) $data['t_last_edit_time'] = strtotime(trim($_REQUEST['t_last_edit_time']));
        if (isset($_REQUEST['t_status'])) $data['t_status'] = intval($_REQUEST['t_status']);
        if (isset($_REQUEST['t_phone_status'])) $data['t_phone_status'] = intval($_REQUEST['t_phone_status']);
        if (isset($_REQUEST['t_phone'])) $data['t_phone'] = trim($_REQUEST['t_phone']);

        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $list = $this->tasks_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

}