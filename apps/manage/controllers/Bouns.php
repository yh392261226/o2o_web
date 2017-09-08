<?php
namespace App\Controller;

class Bouns extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->bouns_dao = new \MDAO\Bouns();
        $this->bouns_type_dao = new \MDAO\Bouns_type();
        $this->bouns_data_dao = new \MDAO\Bouns_data();
        //$this->db->debug = 1;
    }

    /**
     * ****[ Bouns_type ]***********************************************************************************************
     */
    public function addType()
    {
        if (isset($_POST['bt_name']))
        {
            $curtime = time();
            $data   = array(
                'bt_name'       => isset($_POST['bt_name']) ? trim($_POST['bt_name']) : '',
                'bt_in_time'    => $curtime,
                'bt_author'     => $_SESSION['manager']['m_id'],
                'bt_start_time' => (isset($_POST['bt_start_time']) && intval($_POST['bt_start_time']) > 0)  ? strtotime($_POST['bt_start_time']) : 0,
                'bt_end_time'   => (isset($_POST['bt_end_time']) && intval($_POST['bt_end_time']) > 0) ? strtotime($_POST['bt_end_time']) : 0,
                'bt_last_editor'=> $_SESSION['manager']['m_id'],
                'bt_last_edit_time' => $curtime,
                'bt_status'     => isset($_POST['bt_status']) ? trim($_POST['bt_status']) : 0,
                'bt_withdraw'   => isset($_POST['bt_withdraw']) ? trim($_POST['bt_withdraw']) : 0,
                'bt_info'       => isset($_POST['bt_info']) ? trim($_POST['bt_info']) : '',

            );

            if ('' == $data['bt_name']) msg('名称不能为空', 0);
            if (isset($_POST['bt_start_time']) && isset($_POST['bt_end_time']) && $_POST['bt_start_time'] != 0 && $_POST['bt_end_time'] != 0 && strtotime($_POST['bt_end_time']) < strtotime($_POST['bt_start_time']))
            {
                //结束时间不能小于开始时间
                msg('结束时间不能小于开始时间', 0);
            }
            $result = $this->bouns_type_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Bouns/listType');
        }
        $this->mydisplay();
    }

    public function editType()
    {
        if (isset($_POST['bt_id']))
        {
            $curtime = time();
            $data   = array(
                'bt_name'       => isset($_POST['bt_name']) ? trim($_POST['bt_name']) : '',
                'bt_start_time' => (isset($_POST['bt_start_time']) && intval($_POST['bt_start_time']) > 0) ? strtotime($_POST['bt_start_time']) : 0,
                'bt_end_time'   => (isset($_POST['bt_end_time']) && intval($_POST['bt_end_time']) > 0) ? strtotime($_POST['bt_end_time']) : 0,
                'bt_last_editor'=> $_SESSION['manager']['m_id'],
                'bt_last_edit_time' => $curtime,
                'bt_status'     => isset($_POST['bt_status']) ? trim($_POST['bt_status']) : 0,
                'bt_withdraw'   => isset($_POST['bt_withdraw']) ? trim($_POST['bt_withdraw']) : 0,
                'bt_info'       => isset($_POST['bt_info']) ? trim($_POST['bt_info']) : '',
            );

            if ('' == $data['bt_name']) msg('名称不能为空', 0);
            if (isset($_POST['bt_start_time']) && isset($_POST['bt_end_time']) && $_POST['bt_start_time'] != 0 && $_POST['bt_end_time'] != 0 && strtotime($_POST['bt_end_time']) < strtotime($_POST['bt_start_time']))
            {
                //结束时间不能小于开始时间
                msg('结束时间不能小于开始时间', 0);
            }

            $param = array(
                'bt_id' => isset($_POST['bt_id']) ? trim($_POST['bt_id']) : 0,
            );

            if (!$param['bt_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->bouns_type_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }
            //SUCCESSFUL
            msg('操作成功', 1, '/Bouns/listType');
        }

        $info = $this->bouns_type_dao->infoData($_REQUEST['bt_id']);
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function delType()
    {
        $result = 0;
        if (isset($_REQUEST['bt_id']))
        {
            if (is_array($_REQUEST['bt_id']) || strpos($_REQUEST['bt_id'], ','))
            {
                $result = $this->bouns_type_dao->delData(array('bt_id' => array('type' => 'in', 'value' => $_REQUEST['bt_id']))); //伪删除
            }
            else
            {
                $result = $this->bouns_type_dao->delData(intval($_REQUEST['bt_id'])); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败,不允许删除', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Bouns/listType');
    }

    public function infoType()
    {
        $info = array();
        if (isset($_REQUEST['bt_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['bt_id']))
            {
                $info = $this->bouns_type_dao->infoData(intval($_REQUEST['bt_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->bouns_type_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function listType()
    {
        $list = $data = array();
        if (isset($_REQUEST['bt_id'])) $data['bt_id'] = array('type' => 'in', value => $_REQUEST['bt_id']);
        if (isset($_REQUEST['bt_name'])) $data['bt_name'] = array('type'=>'like', 'value' => trim($_REQUEST['bt_name']));
        if (isset($_REQUEST['bt_status'])) $data['bt_status'] = intval($_REQUEST['bt_status']);
        if (isset($_REQUEST['bt_withdraw'])) $data['bt_withdraw'] = intval($_REQUEST['bt_withdraw']);
        if (isset($_REQUEST['bt_author'])) $data['bt_author'] = $_REQUEST['bt_author'];
        if (isset($_REQUEST['bt_start_time'])) $data['bt_start_time'] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['bt_start_time']));
        if (isset($_REQUEST['bt_end_time'])) $data['bt_end_time'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['bt_end_time']));
        if (isset($_REQUEST['bt_start_time']) && isset($_REQUEST['bt_end_time']) && $_REQUEST['bt_start_time'] != 0 && $_REQUEST['bt_end_time'] != 0 && strtotime($_REQUEST['bt_end_time']) < strtotime($_REQUEST['bt_start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $list = $this->bouns_type_dao->listData($data);
        //print_r($data);
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

    /**
     * ****[ Bouns ]***********************************************************************************************
     */
    public function add()
    {
        if (isset($_POST['bt_id']))
        {
            $curtime = time();
            $data   = array(
                'bt_id'       => isset($_POST['bt_id']) ? trim($_POST['bt_id']) : '0',
                'b_start_time' => (isset($_POST['b_start_time']) && $_POST['b_start_time'] > 0) ? strtotime($_POST['b_start_time']) : 0,
                'b_end_time'   => (isset($_POST['b_end_time']) && $_POST['b_end_time'] > 0) ? strtotime($_POST['b_end_time']) : 0,
                'b_total'     => isset($_POST['b_total']) ? trim($_POST['b_total']) : 1,
                'b_status'     => isset($_POST['b_status']) ? trim($_POST['b_status']) : 0,
                'b_type'     => isset($_POST['b_type']) ? trim($_POST['b_type']) : 0,
                'b_author'     => $_SESSION['manager']['m_id'],
                'b_in_time'    => $curtime,
                'b_last_editor'=> $_SESSION['manager']['m_id'],
                'b_last_edit_time' => $curtime,
                'b_info'       => isset($_POST['b_info']) ? trim($_POST['b_info']) : '',
            );

            if (intval($data['bt_id']) < 1) msg('请选择类型', 0);
            if ('' == $data['b_total']) msg('请添加数量', 0);
            if (isset($_POST['b_start_time']) && isset($_POST['b_end_time']) && $_POST['b_start_time'] != 0 && $_POST['b_end_time'] != 0 && strtotime($_POST['b_end_time']) < strtotime($_POST['b_start_time']))
            {
                //结束时间不能小于开始时间
                msg('结束时间不能小于开始时间', 0);
            }
            $result = $this->bouns_dao->addData($data);
            if (!$result)
            {
                //FAILED
                msg('操作失败', 0);
            }
            $this->addData($result, $data['b_total'], $data['bt_id']);
            //SUCCESSFUL
            msg('操作成功', 1, '/Bouns/list');
        }
        $types = $this->bouns_type_dao->listData(array('pager' => 0));
        //print_r($types);
        $this->tpl->assign('types', $types);
        $this->mydisplay();
    }

    public function edit()
    {
        if (isset($_POST['b_id']))
        {
            $curtime = time();
            $data   = array(
                'bt_id'       => isset($_POST['bt_id']) ? trim($_POST['bt_id']) : '0',
                'b_start_time' => (isset($_POST['b_start_time']) && $_POST['b_start_time'] > 0) ? strtotime($_POST['b_start_time']) : 0,
                'b_end_time'   => (isset($_POST['b_end_time']) && $_POST['b_end_time'] > 0) ? strtotime($_POST['b_end_time']) : 0,
                'b_total'     => isset($_POST['b_total']) ? trim($_POST['b_total']) : 1,
                'b_status'     => isset($_POST['b_status']) ? trim($_POST['b_status']) : 0,
                'b_type'     => isset($_POST['b_type']) ? trim($_POST['b_type']) : 0,
                'b_last_editor'=> $_SESSION['manager']['m_id'],
                'b_last_edit_time' => $curtime,
                'b_info'       => isset($_POST['b_info']) ? trim($_POST['b_info']) : '',
            );

            if (intval($data['bt_id']) < 1) msg('请选择类型', 0);
            if ('' == $data['b_total']) msg('请添加数量', 0);
            if (isset($_POST['b_start_time']) && isset($_POST['b_end_time']) && $_POST['b_start_time'] != 0 && $_POST['b_end_time'] != 0 && strtotime($_POST['b_end_time']) < strtotime($_POST['b_start_time']))
            {
                //结束时间不能小于开始时间
                msg('结束时间不能小于开始时间', 0);
            }

            $param = array(
                'b_id' => isset($_POST['b_id']) ? trim($_POST['b_id']) : 0,
            );

            if (!$param['b_id']) {
                //FAILED
                msg('操作失败', 0);
            }

            $result = $this->bouns_dao->updateData($data, $param);
            if (!$result) {
                //FAILED
                msg('操作失败', 0);
            }

            if (!in_array($data['b_type'], array(2,3))) //类型不能是线下和第三方才可以删除
            {
                $del_result = $this->bouns_data_dao->delData(array('b_id' => $param['b_id'], 'walk' => array('_where' => array('bd_author' => '0'))));
                if (!$del_result)
                {
                    msg('操作失败，无法删除往期数据', 0);
                }
            }

            $used = $this->bouns_data_dao->countData(array('b_id' => $param['b_id'], 'bd_author' => array('type'=>'notin', 'value'=>0)));
            if ($used > 0)
            {
                $data['b_total'] = $data['b_total'] - $used;
                if ($data['b_total'] < 1)
                {
                    msg('操作成功', 1, '/Bouns/list');
                }
            }
            $this->addData($param['b_id'], $data['b_total'], $data['bt_id']);
            //SUCCESSFUL
            msg('操作成功', 1, '/Bouns/list');
        }

        $info = $this->bouns_dao->infoData($_REQUEST['b_id']);
        $types = $this->bouns_type_dao->listData(array('pager' => 0));
        $this->tpl->assign('types', $types);
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function del()
    {
        $result = 0;
        if (isset($_REQUEST['b_id']))
        {
            $used = $this->bouns_data_dao->countData(array('b_id' => intval($_REQUEST['b_id']), 'bd_author' => array('type'=>'notin', 'value'=>0)));
            if ($used > 0)
            {
                msg('操作失败,已有人使用序列号', 0);
            }

            if (is_array($_REQUEST['b_id']) || strpos($_REQUEST['b_id'], ','))
            {
                $result = $this->bouns_dao->delData(array('b_id' => array('type' => 'in', 'value' => $_REQUEST['b_id']))); //伪删除
            }
            else
            {
                $result = $this->bouns_dao->delData(intval($_REQUEST['b_id'])); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败,不允许删除', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Bouns/list');
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['b_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['b_id']))
            {
                $info = $this->bouns_dao->infoData(intval($_REQUEST['b_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->bouns_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['bt_id'])) $data['bt_id'] = intval($_REQUEST['bt_id']);
        if (isset($_REQUEST['b_info'])) $data['b_info'] = array('type'=>'like', 'value' => trim($_REQUEST['b_info']));
        if (isset($_REQUEST['b_status'])) $data['b_status'] = intval($_REQUEST['b_status']);
        if (isset($_REQUEST['b_author'])) $data['b_author'] = $_REQUEST['b_author'];
        if (isset($_REQUEST['b_total'])) $data['b_total'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['b_total']));
        if (isset($data['b_total']) && intval($data['b_total']) < 1)
        {
            msg('数量不能小于1', 0);
        }
        if (isset($_REQUEST['b_start_time'])) $data['b_start_time'] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['b_start_time']));
        if (isset($_REQUEST['b_end_time'])) $data['b_end_time'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['b_end_time']));
        if (isset($_REQUEST['b_start_time']) && isset($_REQUEST['b_end_time']) && $_REQUEST['b_start_time'] != 0 && $_REQUEST['b_end_time'] != 0 && strtotime($_REQUEST['b_end_time']) < strtotime($_REQUEST['b_start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $list = $this->bouns_dao->listData($data);
        if (!empty($list['data']))
        {
            $types = $this->bouns_type_dao->listData(array('pager' => 0));
            if (!empty($types['data']))
            {
                foreach ($types['data'] as $key => $value)
                {
                    foreach ($list['data'] as $key => $val)
                    {
                        if ($val['bt_id'] == $value['bt_id'])
                        {
                            $list['data'][$key]['bt_name'] = $value['bt_name'];
                        }
                    }
                }
            }
        }
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

    /**
     * ****[ Bouns_data ]***********************************************************************************************
     */
    public function addData($bid, $total, $bt_id)
    {
        if (intval($bid) < 1) return false;
        if (intval($total) < 1) $total = 1;
        $data = array();
        for ($i = 0; $i < $total; $i++)
        {
            $data[$i][] = $bid;
            $data[$i][] = $bid . guid();
            $data[$i][] = $bt_id;
        }
        $result = $this->bouns_data_dao->addData($data, array('b_id', 'bd_serial', 'bt_id'));
        return false;
    }

    public function delData()
    {
        $result = 0;
        if (isset($_REQUEST['bd_id']))
        {
            if (is_array($_REQUEST['bd_id']) || strpos($_REQUEST['bd_id'], ','))
            {
                $result = $this->bouns_data_dao->delData(array('bd_id' => array('type' => 'in', 'value' => $_REQUEST['bd_id']))); //伪删除
            }
            else
            {
                $result = $this->bouns_data_dao->delData(intval($_REQUEST['bd_id'])); //伪删除
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败,不允许删除', 0);
        }
        //SUCCESSFUL
        msg('操作成功', 1, '/Bouns/listData');
    }


    public function listData()
    {
        $list = $data = array();
        if (isset($_REQUEST['b_id'])) $data['b_id'] = intval($_REQUEST['b_id']);
        if (isset($_REQUEST['bd_serial'])) $data['bd_serial'] = trim($_REQUEST['bd_serial']);
        if (isset($_REQUEST['b_status'])) $data['b_status'] = intval($_REQUEST['b_status']);
        if (isset($_REQUEST['bd_author'])) $data['bd_author'] = $_REQUEST['bd_author'];
        if (isset($_REQUEST['bd_use_time'])) $data['bd_use_time'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['bd_use_time']));
        if (isset($data['bd_use_time']) && intval($data['bd_use_time']) <= 0)
        {
            msg('使用时间不能小于0', 0);
        }
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $list = $this->bouns_data_dao->listData($data);
        if (!empty($list['data']))
        {
            $types = $this->bouns_type_dao->listData(array('pager' => 0));
            if (!empty($types['data']))
            {
                foreach ($types['data'] as $key => $value)
                {
                    foreach ($list['data'] as $key => $val)
                    {
                        if ($val['bt_id'] == $value['bt_id'])
                        {
                            $list['data'][$key]['bt_name'] = $value['bt_name'];
                        }
                    }
                }
            }
        }
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

}