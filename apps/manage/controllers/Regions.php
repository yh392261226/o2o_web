<?php
namespace App\Controller;

class Regions extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->regions_dao = new \MDAO\Regions();
        //$this->db->debug = 1;
    }

    /**
     * ****[ regions ]***********************************************************************************************
     */
    public function add()
    {
        if (isset($_POST['r_name']))
        {
            $data   = array(
                'r_pid'        => isset($_POST['r_pid']) ? trim($_POST['r_pid']) : 1,
                'r_shortname'  => (isset($_POST['r_name']) && trim($_POST['r_name']) != '') ? \MLIB\CUtf8_PY::encode(trim($_POST['r_name'])) : '',
                'r_name'       => isset($_POST['r_name']) ? trim($_POST['r_name']) : '',
                'r_first'      => (isset($_POST['r_name']) && trim($_POST['r_name']) != '') ? \MLIB\CUtf8_PY::encode(trim($_POST['r_name']), 'first') : '',
                'r_status'     => isset($_POST['r_status']) ? trim($_POST['r_status']) : 0,
            );

            if ('' == $data['r_name']) msg('名称不能为空', 0);
            if ($this->regions_dao->checkRegionName(array('r_name' => $data['r_name']))) msg('名称已被占用', 0);

            $result = $this->regions_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Regions/list');
        }
        $regions = $this->regions_dao->listData();
        $this->tpl->assign('regions', $regions);
        $this->mydisplay();
    }

    public function edit()
    {
        if (isset($_POST['r_id']))
        {
            $data   = array(
                'r_pid'        => isset($_POST['r_pid']) ? trim($_POST['r_pid']) : 1,
                'r_shortname'  => (isset($_POST['r_name']) && trim($_POST['r_name']) != '') ? \MLIB\CUtf8_PY::encode(trim($_POST['r_name'])) : '',
                'r_name'       => isset($_POST['r_name']) ? trim($_POST['r_name']) : '',
                'r_first'      => (isset($_POST['r_name']) && trim($_POST['r_name']) != '') ? \MLIB\CUtf8_PY::encode(trim($_POST['r_name']), 'first') : '',
                'r_status'     => isset($_POST['r_status']) ? trim($_POST['r_status']) : 0,
            );

            $param = array(
                'r_id' => isset($_POST['r_id']) ? trim($_POST['r_id']) : 0,
            );

            if (!$param['r_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->regions_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Regions/list');
        }

        $regions = $this->regions_dao->listData(array('r_id' => array('type' => 'notin', 'value' => $_REQUEST['r_id']), 'pager' => 0));
        $info = $this->regions_dao->infoData($_REQUEST['r_id']);
        $this->tpl->assign('info', $info);
        $this->tpl->assign('regions', $regions);
        $this->mydisplay();
    }

    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['r_id']))
        {
            if (is_array($_REQUEST['r_id']) || strpos($_REQUEST['r_id'], ','))
            {
                $result = $this->regions_dao->delData(array('r_id' => array('type' => 'in', 'value' => $_REQUEST['r_id']))); //伪删除
            }
            else
            {
                $result = $this->regions_dao->delData(array('r_id' => intval($_REQUEST['r_id']))); //伪删除
            }
        }

        if (!$result) {
            //FAILED
            msg('操作失败,不允许删除', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Regions/list');
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['r_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['r_id']))
            {
                $info = $this->regions_dao->infoData(intval($_REQUEST['r_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->regions_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay('info');
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['r_id'])) $data['r_id'] = array('type' => 'in', value => $_REQUEST['r_id']);
        if (isset($_REQUEST['r_pid'])) $data['r_pid'] = $_REQUEST['r_pid'];
        if (isset($_REQUEST['r_shortname'])) $data['r_shortname'] = trim($_REQUEST['r_shortname']);
        if (isset($_REQUEST['r_name'])) $data['r_name'] = array('type'=>'like', 'value' => trim($_REQUEST['r_name']));
        if (isset($_REQUEST['r_status'])) $data['r_status'] = intval($_REQUEST['r_status']);
        if (isset($_REQUEST['r_first'])) $data['r_first'] = $_REQUEST['r_first'];
        if (isset($_REQUEST['mpg_id'])) $data['mpg_id'] = intval($_REQUEST['mpg_id']);

        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $data['order'] = 'r_id asc';

        $list = $this->regions_dao->listData($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

}