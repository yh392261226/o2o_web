<?php
namespace App\Controller;

class Managers extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->managers_dao = new \MDAO\Managers();
        $this->managers_privileges_group_dao = new \MDAO\Managers_privileges_group();
        $this->manager_privileges_modules = new \MDAO\Manager_privileges_modules();
    }

    /**
     * ****[ managers ]***********************************************************************************************
     */
    public function login()
    {
        if (isset($_POST['m_name']))
        {
            $data = array(
                'm_name' => $_POST['m_name'],
                'm_pass' => encyptPassword($_POST['m_pass']),
            );
            $result  = $this->managers_dao->infoData(array('key' => 'm_name', 'val' =>  $data['m_name']));
            $_SESSION['manager']
            if (!empty($result) && $result['m_pass'] == $data['m_pass'])
            {
                $_SESSION['manager'] = $result;
            }
            return $_SESSION['manager'];
        }

        if (isset($_SESSION['manager']) && !empty($_SESSION['manager']) && isset($_SESSION['manager']['m_id']) && intval($_SESSION['manager']['m_id']) > 0)
        {
            header('location:/Managers/index');
        }

        $this->tpl->display('login');
    }

    public function add()
    {
        if (isset($_POST['m_name']))
        {
            $curtime = time();
            $data   = array(
                'm_name'    => isset($_POST['m_name']) ? trim($_POST['m_name']) : '',
                'm_pass'    => isset($_POST['m_pass']) ? encyptPassword(trim($_POST['m_pass'])) : '',
                'm_status'  => isset($_POST['m_status']) ? trim($_POST['m_status']) : 0,
                'm_in_time' => $curtime,
                'm_inip'    => getIp(),
                'm_author'  => $_SESSION['manager']['m_id'],
                'mpg_id'    => isset($_POST['mpg_id']) ? trim($_POST['mpg_id']) : 0,
                'm_start_time'      => isset($_POST['m_start_time']) ? trim($_POST['m_start_time']) : 0,
                'm_end_time'        => isset($_POST['m_end_time']) ? trim($_POST['m_end_time']) : 0,
                'm_last_edit_time'  => $curtime,
                'm_last_editor'     => $_SESSION['manager']['m_id'],
                'm_last_ip'         => getIp(),
            );
            $result = $this->managers_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1);
        }
        $this->tpl->display('add');
    }

    public function edit()
    {
        if (isset($_POST['m_id']))
        {
            $curtime = time();
            $data = array(
                'm_pass'           => isset($_POST['m_pass']) ? encyptPassword(trim($_POST['m_pass'])) : '',
                'm_status'         => isset($_POST['m_status']) ? trim($_POST['m_status']) : 0,
                'mpg_id'           => isset($_POST['mpg_id']) ? trim($_POST['mpg_id']) : 0,
                'm_start_time'     => isset($_POST['m_start_time']) ? trim($_POST['m_start_time']) : 0,
                'm_end_time'       => isset($_POST['m_end_time']) ? trim($_POST['m_end_time']) : 0,
                'm_last_edit_time' => $curtime,
                'm_last_editor'    => $_SESSION['manager']['m_id'],
                'm_last_ip'         => getIp(),
            );
            $param = array(
                'm_id' => isset($_POST['m_id']) ? trim($_POST['m_id']) : 0,
            );

            if (!$param['m_id'] || $data['m_pass'] == '') {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->managers_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1);
        }

        $info = $this->managers_dao->infoData($_REQUEST['m_id']);
        $this->tpl->assign('info' => $info);
        $this->tpl->display('edit');
    }

    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['m_id']))
        {
            if (is_array($_REQUEST['m_id']))
            {
                $result = $this->managers_dao->delData(array('m_id' => array('type' => 'in', 'value' => $_REQUEST['m_id']))); //伪删除
            }
            else
            {
                $result = $this->managers_dao->delData(intval($_REQUEST['m_id'])); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1);
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['m_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['m_id']))
            {
                $info = $this->managers_dao->infoData(intval($_REQUEST['m_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->managers_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->tpl->display('info');
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['m_id'])) $data['m_id'] = array('type' => 'in', value => $_REQUEST['m_id']);
        if (isset($_REQUEST['m_name'])) $data['m_name'] = array('type'=>'like', 'value' => trim($_REQUEST['m_name']));
        if (isset($_REQUEST['m_status'])) $data['m_status'] = intval($_REQUEST['m_status']);
        if (isset($_REQUEST['m_inip'])) $data['m_inip'] = array('type' => 'in', 'value' => $_REQUEST['m_inip']);
        if (isset($_REQUEST['m_author'])) $data['m_author'] = intval($_REQUEST['m_author']);
        if (isset($_REQUEST['mpg_id'])) $data['mpg_id'] = intval($_REQUEST['mpg_id']);
        if (isset($_REQUEST['m_start_time'])) $data['m_start_time'] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['m_start_time']));
        if (isset($_REQUEST['m_end_time'])) $data['m_end_time'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['m_end_time']));

        if (isset($_REQUEST['m_start_time']) && isset($_REQUEST['m_end_time']) && strtotime($_REQUEST['m_end_time']) < strtotime($_REQUEST['m_start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }

        $list = $this->managers_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->tpl->display('list');
    }

    /**
     * ****[ managers privileges group ]***********************************************************************************************
     */

    public function addGroup()
    {
        if (isset($_POST['mpg_id']))
        {
            $curtime = time();
            $mpm_ids = isset($_POST['mpm_ids']) ? $_POST['mpm_ids'] : '';
            if (is_array($mpm_ids))
            {
                $mpm_ids = implode(',', $mpm_ids);
            }

            $data   = array(
                'mpg_name'          => isset($_POST['mpg_name']) ? trim($_POST['mpg_name']) : '',
                'mpg_status'        => isset($_POST['mpg_status']) ? trim($_POST['mpg_status']) : 0,,
                'mpg_in_time'       => $curtime,
                'mpg_author'        => $_SESSION['manager']['m_id'],
                'mpg_last_edit_time'=> $curtime,
                'mpg_last_editor'   => $_SESSION['manager']['m_id'],
                'mpm_ids'           => $mpm_ids,
            );
            $result = $this->managers_privileges_group_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1);
        }

        $this->tpl->display('group_add');
    }

    public function editGroup()
    {
        if (isset($_POST['mpg_id']))
        {
            $curtime = time();
            $mpg_ids = isset($_POST['mpm_ids']) ? $_POST['mpm_ids'] : '';
            if (is_array($mpg_ids))
            {
                $mpg_ids = implode(',', $mpg_ids);
            }

            $data   = array(
                'mpg_name'          => isset($_POST['mpg_name']) ? trim($_POST['mpg_name']) : '',
                'mpg_status'        => isset($_POST['mpg_status']) ? trim($_POST['mpg_status']) : '0',
                'mpg_last_edit_time'=> $curtime,
                'mpg_last_editor'   => $_SESSION['manager']['m_id'],
                'mpm_ids'           => $mpm_ids,
            );
            $param = array(
                'mpg_id' => isset($_POST['mpg_id']) ? trim($_POST['mpg_id']) : 0,
            );

            if (!$param['mpg_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->managers_privileges_group_dao->updateData($data, $param);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1);
        }
        $info = $this->managers_privileges_group_dao->infoData($_REQUEST['mpg_id']);
        $this->tpl->assign('info' => $info);
        $this->tpl->display('group_edit');
    }

    public function delGroup()
    {
        $result = 0;
        if (isset($_REQUEST['mpg_id']))
        {
            if (is_array($_REQUEST['mpg_id']))
            {
                $result = $this->managers_privileges_group_dao->delData(array('mpg_id' => array('type' => 'in', 'value' => $_REQUEST['mpg_id'])));
            }
            else
            {
                $result = $this->managers_privileges_group_dao->delData(intval($_REQUEST['mpg_id']));
            }
        }

        if (!$result) {
            //FAILED
            msg('操作失败', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1);
    }

    public function infoGroup()
    {
        $info = array();
        if (isset($_REQUEST['mpg_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['mpg_id']))
            {
                $info = $this->managers_privileges_group_dao->infoData(intval($_REQUEST['mpg_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->managers_privileges_group_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->tpl->display('group_info');
    }

    public function listGroup()
    {
        $list = $data = array();
        if (isset($_REQUEST['mpg_id'])) $data['mpg_id'] = array('type' => 'in', value => $_REQUEST['mpg_id']);
        if (isset($_REQUEST['mpg_name'])) $data['mpg_name'] = array('type'=>'like', 'value' => trim($_REQUEST['mpg_name']));
        if (isset($_REQUEST['mpg_status'])) $data['mpg_status'] = intval($_REQUEST['mpg_status']);
        if (isset($_REQUEST['mpg_author'])) $data['mpg_author'] = intval($_REQUEST['mpg_author']);
        if (isset($_REQUEST['mpg_in_time'])) $data['mpg_in_time'] = array(
            array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_mpg_in_time']),
            array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_mpg_in_time'])
        );

        if (isset($_REQUEST['m_start_time']) && isset($_REQUEST['m_end_time']) && strtotime($_REQUEST['m_end_time']) < strtotime($_REQUEST['m_start_time']))
        {
            //结束时间不能小于开始时间
            //failed
            msg('结束时间不能小于开始时间', 0);
        }

        $list = $this->managers_privileges_group_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->tpl->display('group_list');
    }

    /**
     * ****[ managers privileges modules ]***********************************************************************************************
     */

    public function addModules()
    {
        if (isset($_POST['mpm_name']))
        {
            $curtime = time();
            $data   = array(
                'mpm_name'  => isset($_POST['mpm_name']) ? trim($_POST['mpm_name']) : '',
                'mpm_value' => isset($_POST['mpm_value']) ? trim($_POST['mpm_value']) : '',
                'mpm_status'=> isset($_POST['mpm_status']) ? intval($_POST['mpm_status']) : '0',
            );
            $result = $this->manager_privileges_modules->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1);
        }
        $this->tpl->display('modules_add');
    }

    public function editMoudles()
    {
        if (isset($_POST['mpm_id']))
        {
            $curtime = time();
            $data   = array(
                'mpm_name'  => isset($_POST['mpm_name']) ? trim($_POST['mpm_name']) : '',
                'mpm_value' => isset($_POST['mpm_value']) ? trim($_POST['mpm_value']) : '',
                'mpm_status'=> isset($_POST['mpm_status']) ? intval($_POST['mpm_status']) : '0',
            );
            $param = array(
                'mpm_id' => isset($_POST['mpm_id']) ? trim($_POST['mpm_id']) : 0,
            );

            if (!$param['mpm_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->manager_privileges_modules->updateData($data, $param);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1);
        }
        $info = $this->manager_privileges_modules->infoData($_REQUEST['mpm_id']);
        $this->tpl->assign('info' => $info);
        $this->tpl->display('modules_edit');
    }

    public function delModules()
    {
        $result = 0;
        if (isset($_REQUEST['mpm_id']))
        {
            if (is_array($_REQUEST['mpm_id']))
            {
                $result = $this->manager_privileges_modules->delData(array('mpm_id' => array('type' => 'in', 'value' => $_REQUEST['mpm_id'])));
            }
            else
            {
                $result = $this->manager_privileges_modules->delData(intval($_REQUEST['mpm_id']));
            }
        }

        if (!$result) {
            //FAILED
            msg('操作失败', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1);
    }

    public function infoModules()
    {
        $info = array();
        if (isset($_REQUEST['mpm_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['mpm_id']))
            {
                $info = $this->manager_privileges_modules->infoData(intval($_REQUEST['mpm_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->manager_privileges_modules->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->tpl->display('info');
    }

    public function listModules()
    {
        $list = $data = array();
        if (isset($_REQUEST['mpm_id'])) $data['mpm_id'] = array('type' => 'in', value => $_REQUEST['mpm_id']);
        if (isset($_REQUEST['mpm_name'])) $data['mpm_name'] = array('type'=>'like', 'value' => trim($_REQUEST['mpm_name']));
        if (isset($_REQUEST['mpm_status'])) $data['mpm_status'] = intval($_REQUEST['mpm_status']);
        if (isset($_REQUEST['mpm_value'])) $data['mpm_value'] = array('type' => 'like', 'value' => $_REQUEST['mpm_value']);
        $list = $this->manager_privileges_modules->listData($data);
        $this->tpl->assign('list', $list);
        $this->tpl->display('list');
    }


}