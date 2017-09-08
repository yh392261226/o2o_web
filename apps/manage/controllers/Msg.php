<?php
namespace App\Controller;

class Msg extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->web_msg_dao = new \MDAO\Web_msg();
        $this->web_msg_ext_dao = new \MDAO\Web_msg_ext();
        //$this->db->debug = 1;
    }

    /**
     * ****[ web_msg ]***********************************************************************************************
     */
    public function add()
    {
        if (isset($_POST['wm_title']))
        {
            $curtime = time();
            $data   = array(
                'wm_title'     => isset($_POST['wm_title']) ? trim($_POST['wm_title']) : '',
                'wm_in_time'   => $curtime,
                'wm_author'    => $_SESSION['manager']['m_id'],
                'wm_type'      => isset($_POST['wm_type']) ? trim($_POST['wm_type']) : '0',
                'wm_status'    => isset($_POST['wm_status']) ? trim($_POST['wm_status']) : 0,
                'wm_start_time'=> (isset($_POST['wm_start_time']) && intval($_POST['wm_start_time']) > 0) ? strtotime($_POST['wm_start_time']) : '0',
                'wm_end_time'  => (isset($_POST['wm_end_time']) && intval($_POST['wm_end_time']) > 0) ? strtotime($_POST['wm_end_time']) : '0',
            );
            $desc = array(
                'wm_desc' => isset($_POST['wm_desc']) ? trim($_POST['wm_desc']) : '',
            );

            if ('' == $data['wm_title']) msg('标题不能为空', 0);

            $result = $this->web_msg_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            $this->web_msg_ext_dao->addData(array('wm_id' => $result, 'wm_desc' => $desc['wm_desc']));
            msg('操作成功', 1, '/Msg/list');
        }

        $this->mydisplay();
    }

    public function edit()
    {
        if (isset($_POST['wm_id']))
        {
            $curtime = time();
            $data   = array(
                'wm_title'     => isset($_POST['wm_title']) ? trim($_POST['wm_title']) : '',
                'wm_in_time'   => $curtime,
                'wm_author'    => $_SESSION['manager']['m_id'],
                'wm_type'      => isset($_POST['wm_type']) ? trim($_POST['wm_type']) : '0',
                'wm_status'    => isset($_POST['wm_status']) ? trim($_POST['wm_status']) : 0,
                'wm_start_time'=> (isset($_POST['wm_start_time']) && intval($_POST['wm_start_time']) > 0) ? strtotime($_POST['wm_start_time']) : '0',
                'wm_end_time'  => (isset($_POST['wm_end_time']) && intval($_POST['wm_end_time']) > 0) ? strtotime($_POST['wm_end_time']) : '0',
            );
            $desc = array(
                'wm_desc' => isset($_POST['wm_desc']) ? trim($_POST['wm_desc']) : '',
            );

            if ('' == $data['wm_title']) msg('标题不能为空', 0);

            $param = array(
                'wm_id' => isset($_POST['wm_id']) ? trim($_POST['wm_id']) : 0,
            );

            if (!$param['wm_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->web_msg_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            $this->web_msg_ext_dao->updateData($desc, $param);
            msg('操作成功', 1, '/Msg/list');
        }

        $info = $this->web_msg_dao->infoData($_REQUEST['wm_id']);
        if (!empty($info))
        {
            $desc = $this->web_msg_ext_dao->infoData($_REQUEST['wm_id']);
            if (!empty($desc))
            {
                $info['wm_desc'] = $desc['wm_desc'];
            }
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['wm_id']))
        {
            if (is_array($_REQUEST['wm_id']) || strpos($_REQUEST['wm_id'], ','))
            {
                $result = $this->web_msg_dao->delData(array('wm_id' => array('type' => 'in', 'value' => $_REQUEST['wm_id']))); //伪删除
            }
            else
            {
                $result = $this->web_msg_dao->delData(intval($_REQUEST['wm_id'])); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败,不允许删除', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Msg/list');
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['wm_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['wm_id']))
            {
                $info = $this->web_msg_dao->infoData(intval($_REQUEST['wm_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->web_msg_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay('info');
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['wm_id'])) $data['wm_id'] = array('type' => 'in', value => $_REQUEST['wm_id']);
        if (isset($_REQUEST['wm_title'])) $data['wm_title'] = array('type'=>'like', 'value' => trim($_REQUEST['wm_title']));
        if (isset($_REQUEST['wm_author'])) $data['wm_author'] = intval($_REQUEST['wm_author']);
        if (isset($_REQUEST['wm_type'])) $data['wm_type'] = intval($_REQUEST['wm_type']);
        if (isset($_REQUEST['wm_status'])) $data['wm_status'] = intval($_REQUEST['wm_status']);
        if (isset($_REQUEST['wm_start_time'])) $data['wm_start_time'] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['wm_start_time']));
        if (isset($_REQUEST['wm_end_time'])) $data['wm_end_time'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['wm_end_time']));
        if (isset($_REQUEST['wm_start_time']) && isset($_REQUEST['wm_end_time']) && $_REQUEST['wm_start_time'] != 0 && $_REQUEST['wm_end_time'] != 0 && strtotime($_REQUEST['wm_end_time']) < strtotime($_REQUEST['wm_start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $list = $this->web_msg_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

}