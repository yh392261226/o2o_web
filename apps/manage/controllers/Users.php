<?php
namespace App\Controller;

class Users extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->users_dao = new \MDAO\Users();
        $this->users_ext_info_dao = new \MDAO\Users_ext_info();
        $this->users_ext_funds_dao = new \MDAO\Users_ext_funds();
        //$this->db->debug = 1;
    }

    /**
     * ****[ users ]***********************************************************************************************
     */
    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['u_id']))
        {
            if (is_array($_REQUEST['u_id']) || strpos($_REQUEST['u_id'], ','))
            {
                $result = $this->users_dao->delData(array('u_id' => array('type' => 'in', 'value' => $_REQUEST['u_id']))); //伪删除
            }
            else
            {
                $result = $this->users_dao->delData(array('u_id' => intval($_REQUEST['u_id']))); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Users/list');
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['u_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['u_id']))
            {
                $info = $this->users_dao->infoData(intval($_REQUEST['u_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->users_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }

        if (!empty($info))
        {
            $funds = $this->users_ext_funds_dao->infoData(intval($_REQUEST['u_id']));
            if (!empty($funds))
            {
                $info['funds'] = $funds;
            }

            $ext_info = $this->users_ext_info_dao->infoData(intval($_REQUEST['u_id']));
            if (!empty($ext_info))
            {
                $info['ext'] = $ext_info;
            }
            unset($funds, $ext_info);
        }
//print_r($info);
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['u_id'])) $data['u_id'] = array('type' => 'in', value => $_REQUEST['u_id']);
        if (isset($_REQUEST['u_name'])) $data['u_name'] = array('type'=>'like', 'value' => trim($_REQUEST['u_name']));
        if (isset($_REQUEST['u_mobile'])) $data['u_mobile'] = intval($_REQUEST['u_mobile']);
        if (isset($_REQUEST['u_bind_mobile'])) $data['u_bind_mobile'] = intval($_REQUEST['u_bind_mobile']);
        if (isset($_REQUEST['u_phone'])) $data['u_phone'] = trim($_REQUEST['u_phone']);
        if (isset($_REQUEST['u_fax'])) $data['u_fax'] = trim($_REQUEST['u_fax']);
        if (isset($_REQUEST['u_sex'])) $data['u_sex'] = trim($_REQUEST['u_sex']);
        if (isset($_REQUEST['u_online'])) $data['u_online'] = intval($_REQUEST['u_online']);
        if (isset($_REQUEST['u_status'])) $data['u_status'] = intval($_REQUEST['u_status']);
        if (isset($_REQUEST['u_type'])) $data['u_type'] = intval($_REQUEST['u_type']);
        if (isset($_REQUEST['u_task_status'])) $data['u_task_status'] = intval($_REQUEST['u_task_status']);
        if (isset($_REQUEST['u_start'])) $data['u_start'] = intval($_REQUEST['u_start']);
        if (isset($_REQUEST['u_top'])) $data['u_top'] = intval($_REQUEST['u_top']);
        if (isset($_REQUEST['u_recommend'])) $data['u_recommend'] = intval($_REQUEST['u_recommend']);
        if (isset($_REQUEST['u_true_name'])) $data['u_true_name'] = trim($_REQUEST['u_true_name']);
        if (isset($_REQUEST['u_idcard'])) $data['u_idcard'] = trim($_REQUEST['u_idcard']);
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        //可以区间值
        if (isset($_REQUEST['u_in_time'])) $data['u_in_time'] = strtotime(trim($_REQUEST['u_in_time']));
        if (isset($_REQUEST['u_last_edit_time'])) $data['u_last_edit_time'] = strtotime(trim($_REQUEST['u_last_edit_time']));
        if (isset($_REQUEST['u_credit'])) $data['u_credit'] = intval($_REQUEST['u_credit']);
        if (isset($_REQUEST['u_jobs_num'])) $data['u_jobs_num'] = intval($_REQUEST['u_jobs_num']);
        if (isset($_REQUEST['u_worked_num'])) $data['u_worked_num'] = intval($_REQUEST['u_worked_num']);
        if (isset($_REQUEST['u_high_opinions'])) $data['u_high_opinions'] = intval($_REQUEST['u_high_opinions']);
        if (isset($_REQUEST['u_low_opinions'])) $data['u_low_opinions'] = intval($_REQUEST['u_low_opinions']);
        if (isset($_REQUEST['u_middle_opinions'])) $data['u_middle_opinions'] = intval($_REQUEST['u_middle_opinions']);
        if (isset($_REQUEST['u_dissensions'])) $data['u_dissensions'] = intval($_REQUEST['u_dissensions']);

        $list = $this->users_dao->listData($data);//print_r($list);exit;
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

    public function sendMessage()
    {
        $data = array();
        if (isset($_REQUEST['u_id'])) $data['u_id'] = intval($_REQUEST['u_id']);
        if (isset($data['u_id']) && 0 < $data['u_id'])
        {
            if (isset($_REQUEST['content'])) $data['content'] = trim($_REQUEST['content']);
            if (isset($_REQUEST['u_mobile'])) $data['u_mobile'] = trim($_REQUEST['u_mobile']);
            if (!isset($data['content']) || '' == trim($_REQUEST['content']) || !isset($data['u_mobile']) || 13000000000 > intval($_REQUEST['u_mobile']))
            {
                echo 0;exit;
            }
            $result = sendSms(intval($_REQUEST['u_mobile']), trim($data['content']));
            if ($result)
            {
                echo 1;exit;
            }

        }
        echo 0;exit;
    }

}