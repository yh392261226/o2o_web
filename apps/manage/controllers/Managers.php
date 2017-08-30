<?php
namespace App\Controller;

class Managers extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->managers_dao = new \MDAO\Managers();
    }

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

        $this->tpl->display('manager_login');
    }

    public function add()
    {
        if (isset($_POST['m_name']))
        {
            $curtime = time();
            $data   = array(
                'm_name' => isset($_POST['m_name']) ? trim($_POST['m_name']) : '',
                'm_pass' => isset($_POST['m_pass']) ? encyptPassword(trim($_POST['m_pass'])) : '',
                'm_status' => 0,
                'm_in_time' => $curtime,
                'm_inip' => getIp(),
                'm_author' => $_SESSION['manager']['m_id'],
                'mpg_id' => $_POST['mpg_id'],
                'm_start_time' => isset($_POST['m_start_time']) ? intval($_POST['m_start_time']) : 0,
                'm_end_time' => isset($_POST['m_end_time']) ? intval($_POST['m_end_time']) : 0,
                'm_last_edit_time' => $curtime,
                'm_last_editor' => $_SESSION['manager']['m_id']
            );
            $result = $this->managers_dao->addData($data);
            if (!$result)
            {
                //FAILED
            }
            //SUCCESSFUL
        }
        $this->tpl->display('manager_add');
    }

    public function edit()
    {
        if (isset($_POST['m_id']))
        {
            $curtime = time();
            $data = array(
                'm_pass'           => isset($_POST['m_pass']) ? encyptPassword(trim($_POST['m_pass'])) : '',
                'm_status'         => 0,
                'mpg_id'           => $_POST['mpg_id'],
                'm_start_time'     => isset($_POST['m_start_time']) ? intval($_POST['m_start_time']) : 0,
                'm_end_time'       => isset($_POST['m_end_time']) ? intval($_POST['m_end_time']) : 0,
                'm_last_edit_time' => $curtime,
                'm_last_editor'    => $_SESSION['manager']['m_id']
            );
            $param = array(
                'm_id' => isset($_POST['m_id']) ? trim($_POST['m_id']) : 0,
            );

            if (!$param['m_id'] || $data['m_pass'] == '') {
                //FAILED
            }

            $result = $this->managers_dao->upData($data, $param);
            if (!$result) {
                //FAILED
            }
            //SUCCESSFUL
        }

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
        }
        //SUCCESSFUL
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
        $this->tpl->display('manager_info');
    }

    public function list()
    {

    }


}