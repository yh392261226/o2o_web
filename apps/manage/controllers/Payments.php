<?php
namespace App\Controller;

class Payments extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->payments_dao = new \MDAO\Payments();
        //$this->db->debug = 1;
    }

    /**
     * ****[ web_msg ]***********************************************************************************************
     */
    public function add()
    {
        if (isset($_POST['p_name']))
        {
            $curtime = time();
            $data   = array(
                'p_type'    => isset($_POST['p_type']) ? intval($_POST['p_type']) : '0',
                'p_name'    => isset($_POST['p_name']) ? trim($_POST['p_name']) : '',
                'p_info'    => isset($_POST['p_info']) ? trim($_POST['p_info']) : '',
                'p_status'  => isset($_POST['p_status']) ? trim($_POST['p_status']) : 0,
                'p_paras'   => isset($_POST['p_paras']) ? trim($_POST['p_paras']) : '',
                'p_author'  => $_SESSION['manager']['m_id'],
                'p_last_editor'  => $_SESSION['manager']['m_id'],
                'p_last_edit_time'  => $curtime,
                'p_default' => isset($_POST['p_default']) ? intval($_POST['p_default']) : '0',
            );

            if ('' == $data['p_name']) msg('名称不能为空', 0);
            if (intval($data['p_default']) > 0) {
                $check = $this->payments_dao->checkDefault($data['p_type']);
                if ($check) {
                    msg('当前类型已有默认', 0);
                }
            }

            $result = $this->payments_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Payments/list');
        }

        $this->mydisplay();
    }

    public function edit()
    {
        if (isset($_POST['p_id']))
        {
            $curtime = time();
            $data   = array(
                'p_type'    => isset($_POST['p_type']) ? intval($_POST['p_type']) : '0',
                'p_name'    => isset($_POST['p_name']) ? trim($_POST['p_name']) : '',
                'p_info'    => isset($_POST['p_info']) ? trim($_POST['p_info']) : '',
                'p_status'  => isset($_POST['p_status']) ? trim($_POST['p_status']) : 0,
                'p_paras'   => isset($_POST['p_paras']) ? trim($_POST['p_paras']) : '',
                //'p_author'  => $_SESSION['manager']['m_id'],
                'p_last_editor'  => $_SESSION['manager']['m_id'],
                'p_last_edit_time'  => $curtime,
                'p_default' => isset($_POST['p_default']) ? intval($_POST['p_default']) : '0',
            );
            if ('' == $data['p_name']) msg('名称不能为空', 0);

            $param = array(
                'p_id' => isset($_POST['p_id']) ? trim($_POST['p_id']) : 0,
            );

            if (!$param['p_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            if (intval($data['p_default']) > 0)
            {
                $check = $this->payments_dao->checkDefault($data['p_type'], $param['p_id']);
                if ($check)
                {
                    msg('当前类型已有默认', 0);
                }
            }

            $result = $this->payments_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Payments/list');
        }

        $info = $this->payments_dao->infoData($_REQUEST['p_id']);
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['p_id']))
        {
            if (is_array($_REQUEST['p_id']) || strpos($_REQUEST['p_id'], ','))
            {
                $result = $this->payments_dao->delData(array('p_id' => array('type' => 'in', 'value' => $_REQUEST['p_id']))); //伪删除
            }
            else
            {
                $result = $this->payments_dao->delData(intval($_REQUEST['p_id'])); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败,不允许删除', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Payments/list');
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['p_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['p_id']))
            {
                $info = $this->payments_dao->infoData(intval($_REQUEST['p_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->payments_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay('info');
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['p_id'])) $data['p_id'] = array('type' => 'in', value => $_REQUEST['p_id']);
        if (isset($_REQUEST['p_name'])) $data['p_name'] = array('type'=>'like', 'value' => trim($_REQUEST['p_name']));
        if (isset($_REQUEST['p_type'])) $data['p_type'] = intval($_REQUEST['p_type']);
        if (isset($_REQUEST['p_status'])) $data['p_status'] = intval($_REQUEST['p_status']);
        if (isset($_REQUEST['p_default'])) $data['p_default'] = intval($_REQUEST['p_default']);
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $list = $this->payments_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

}