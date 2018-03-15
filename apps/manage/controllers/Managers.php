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

        //$this->db->debug = 1;
    }

    /**
     * ****[ managers ]***********************************************************************************************
     */

    public function index()
    {
        $this->mydisplay();
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
            $_SESSION['manager'] = array();
            if (!empty($result) && $result['m_pass'] == $data['m_pass'] && $result['m_id'] > 0)
            {
                if (isset($result['m_status']))
                {
                    switch ($result['m_status'])
                    {
                        case '-2': //假删除的
                            echo json_encode(array('status' => 200, 'data' => '当前用户被冻结，请联系管理员处理'));exit;
                            break;
                        case '-1': //禁止登陆的
                            echo json_encode(array('status' => 200, 'data' => '当前用户被禁止登陆，请联系管理员处理'));exit;
                            break;
                        case '0': //普通管理员

                            break;
                        case '1': //限制类的管理员

                            break;
                        case '9': //总管理员

                            break;
                    }
                }

                $_SESSION['manager'] = $result;
                if (isset($_SESSION['manager']) && !empty($_SESSION['manager']) && isset($_SESSION['manager']['m_id']) && intval($_SESSION['manager']['m_id']) > 0)
                {
                    parent::$manager_status = $result['m_id'];
                    echo json_encode(array('status' => 200, 'data' => 'success', 'url' => HOSTURL . '/Managers/index'));exit;
                }
                echo json_encode(array('status' => 200, 'data' => '数据信息错误，请联系管理员处理'));exit;
            }
            echo json_encode(array('status' => 200, 'data' => '错误的账号或密码'));exit;
        }

        if (isset($_SESSION['manager']) && !empty($_SESSION['manager']) && isset($_SESSION['manager']['m_id']) && intval($_SESSION['manager']['m_id']) > 0)
        {
            header('location:' . HOSTURL . '/Managers/index');
        }

        $this->mydisplay();
    }

    public function logOut()
    {
        session_destroy();
        session_unset();
        $this->http->redirect(HOSTURL);
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
                'm_author'  => parent::$manager_status,
                'mpg_id'    => isset($_POST['mpg_id']) ? trim($_POST['mpg_id']) : 0,
                'm_start_time'      => isset($_POST['m_start_time']) ? trim($_POST['m_start_time']) : 0,
                'm_end_time'        => isset($_POST['m_end_time']) ? trim($_POST['m_end_time']) : 0,
                'm_last_edit_time'  => $curtime,
                'm_last_editor'     => parent::$manager_status,
                'm_last_ip'         => getIp(),
            );

            if ('' == $data['m_name']) msg('管理员名称不能为空', 0);
            if ('' == $data['m_pass']) msg('密码不能为空', 0);
            if (isset($_POST['m_start_time']) && isset($_POST['m_end_time']) && $_POST['m_start_time'] != 0 && $_POST['m_end_time'] != 0 && strtotime($_POST['m_end_time']) < strtotime($_POST['m_start_time']))
            {
                //结束时间不能小于开始时间
                msg('结束时间不能小于开始时间', 0);
            }
            if ($this->managers_dao->checkManagerName($data['m_name'])) msg('管理员名称已被占用', 0);

            $result = $this->managers_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Managers/list');
        }
        $privileges_group = $this->managers_privileges_group_dao->listDataAll();
        $this->tpl->assign('group', $privileges_group);
        $this->mydisplay();
    }

    public function edit()
    {
        if (isset($_POST['m_id']))
        {
            $curtime = time();
            $data = array(
                'm_pass'           => (isset($_POST['m_pass']) && '' != trim($_POST['m_pass'])) ? encyptPassword(trim($_POST['m_pass'])) : '',
                'm_status'         => isset($_POST['m_status']) ? trim($_POST['m_status']) : 0,
                'mpg_id'           => isset($_POST['mpg_id']) ? intval($_POST['mpg_id']) : 0,
                'm_start_time'     => isset($_POST['m_start_time']) ? trim($_POST['m_start_time']) : 0,
                'm_end_time'       => isset($_POST['m_end_time']) ? trim($_POST['m_end_time']) : 0,
                'm_last_edit_time' => $curtime,
                'm_last_editor'    => parent::$manager_status,
                'm_last_ip'         => getIp(),
            );

            if ('' == $data['m_pass']) unset($data['m_pass']);
            if (isset($_POST['m_start_time']) && isset($_POST['m_end_time']) && $_POST['m_start_time'] != 0 && $_POST['m_end_time'] != 0 && strtotime($_POST['m_end_time']) < strtotime($_POST['m_start_time']))
            {
                //结束时间不能小于开始时间
                msg('结束时间不能小于开始时间', 0);
            }
            $param = array(
                'm_id' => isset($_POST['m_id']) ? trim($_POST['m_id']) : 0,
            );

            if (!$param['m_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->managers_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Managers/list');
        }

        $info = $this->managers_dao->infoData($_REQUEST['m_id']);
        $privileges_group = $this->managers_privileges_group_dao->listDataAll();
        $this->tpl->assign('info', $info);
        $this->tpl->assign('group', $privileges_group);
        $this->mydisplay();
    }

    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['m_id']))
        {
            if (is_array($_REQUEST['m_id']) || strpos($_REQUEST['m_id'], ','))
            {
                $result = $this->managers_dao->delData(array('m_id' => array('type' => 'in', 'value' => $_REQUEST['m_id']))); //伪删除
            }
            else
            {
                $result = $this->managers_dao->delData(array('m_id' => intval($_REQUEST['m_id']))); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Managers/list');
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
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $data['where'] = ' m_id != 1 and m_id != ' . $_SESSION['manager']['m_id'];
//        $data['notin'] = array('m_status', '-2');
        if (isset($data['m_status']) && '' != $data['m_status'])
        {
            unset($data['notin']);
        }

        if (isset($_REQUEST['m_start_time']) && isset($_REQUEST['m_end_time']) && $_REQUEST['m_start_time'] != 0 && $_REQUEST['m_end_time'] != 0 && strtotime($_REQUEST['m_end_time']) < strtotime($_REQUEST['m_start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }

        $list = $this->managers_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

    /**
     * ****[ managers privileges group ]***********************************************************************************************
     */

    public function addGroup()
    {
        if (isset($_POST['mpg_name']))
        {
            $curtime = time();
            $mpm_ids = isset($_POST['mpm_ids']) ? $_POST['mpm_ids'] : '';
            if (is_array($mpm_ids))
            {
                $mpm_ids = implode(',', $mpm_ids);
            }

            $data   = array(
                'mpg_name'          => isset($_POST['mpg_name']) ? trim($_POST['mpg_name']) : '',
                'mpg_status'        => isset($_POST['mpg_status']) ? trim($_POST['mpg_status']) : 0,
                'mpg_in_time'       => $curtime,
                'mpg_author'        => parent::$manager_status,
                'mpg_last_edit_time'=> $curtime,
                'mpg_last_editor'   => parent::$manager_status,
                'mpm_ids'           => $mpm_ids,
            );
            $result = $this->managers_privileges_group_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, 'Managers/listGroup');
        }

        $modules = $this->manager_privileges_modules->listData(array('pager' => false));
        //print_r($modules);
        $this->tpl->assign('modules', $modules);
        $this->mydisplay();
    }

    public function editGroup()
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
                'mpg_status'        => isset($_POST['mpg_status']) ? trim($_POST['mpg_status']) : '0',
                'mpg_last_edit_time'=> $curtime,
                'mpg_last_editor'   => parent::$manager_status,
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
            msg('操作成功', 1, 'Managers/listGroup');
        }
        $info = $this->managers_privileges_group_dao->infoData($_REQUEST['mpg_id']);
        $modules = $this->manager_privileges_modules->listData(array('pager' => false));
        $infomodules = array();
        if (isset($info['mpm_ids']))
        {
            $infomodules = explode(',', $info['mpm_ids']);
        }
        $this->tpl->assign('infomodules', $infomodules);
        $this->tpl->assign('info', $info);
        $this->tpl->assign('modules', $modules);
        $this->mydisplay();
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
        $this->tpl->display();
    }

    public function listGroup()
    {
        $list = $data = array();
        if (isset($_REQUEST['mpg_id'])) $data['mpg_id'] = array('type' => 'in', value => $_REQUEST['mpg_id']);
        if (isset($_REQUEST['mpg_name'])) $data['like'] = array('mpg_name', trim($_REQUEST['mpg_name']));
        if (isset($_REQUEST['mpg_status'])) $data['mpg_status'] = intval($_REQUEST['mpg_status']);
        if (isset($_REQUEST['mpg_author'])) $data['mpg_author'] = intval($_REQUEST['mpg_author']);
        if (isset($_REQUEST['mpg_in_time'])) $data['mpg_in_time'] = array(
            array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_mpg_in_time'])),
            array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_mpg_in_time'])),
        );
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $list = $this->managers_privileges_group_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
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
            $desc = array(
                'mpm_desc' =>  isset($_POST['mpm_desc']) ? trim($_POST['mpm_desc']) : '',
            );
            $result = $this->manager_privileges_modules->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            $this->manager_privileges_modules_desc_dao = new \MDAO\Manager_privileges_modules_desc();
            $this->manager_privileges_modules_desc_dao->addData(array('mpm_id' => $result, 'mpm_desc' => $desc['mpm_desc']));
            msg('操作成功', 1, '/Managers/listModules');
        }
        $this->mydisplay();
    }

    public function editModules()
    {
        $this->manager_privileges_modules_desc_dao = new \MDAO\Manager_privileges_modules_desc();
        if (isset($_POST['mpm_id']))
        {
            $curtime = time();
            $data   = array(
                'mpm_name'  => isset($_POST['mpm_name']) ? trim($_POST['mpm_name']) : '',
                'mpm_value' => isset($_POST['mpm_value']) ? trim($_POST['mpm_value']) : '',
                'mpm_status'=> isset($_POST['mpm_status']) ? intval($_POST['mpm_status']) : '0',
            );
            $desc = array(
                'mpm_desc' =>  isset($_POST['mpm_desc']) ? trim($_POST['mpm_desc']) : '',
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
            $this->manager_privileges_modules_desc_dao->updateData($desc, $param);
            msg('操作成功', 1, '/Managers/listModules');
        }
        $info = $this->manager_privileges_modules->infoData($_REQUEST['mpm_id']);
        if (!empty($info))
        {
            $desc = $this->manager_privileges_modules_desc_dao->infoData($_REQUEST['mpm_id']);
            if (!empty($desc)) $info['mpm_desc'] = $desc['mpm_desc'];
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay();
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
        $this->tpl->display();
    }

    public function listModules()
    {
        $list = $data = array();
        if (isset($_REQUEST['mpm_id'])) $data['mpm_id'] = array('type' => 'in', value => $_REQUEST['mpm_id']);
        if (isset($_REQUEST['mpm_name'])) $data['mpm_name'] = array('type'=>'like', 'value' => trim($_REQUEST['mpm_name']));
        if (isset($_REQUEST['mpm_status'])) $data['mpm_status'] = intval($_REQUEST['mpm_status']);
        if (isset($_REQUEST['mpm_value'])) $data['mpm_value'] = array('type' => 'like', 'value' => $_REQUEST['mpm_value']);
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $list = $this->manager_privileges_modules->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

    /**
     * ****[ others ]***********************************************************************************************
     */

}