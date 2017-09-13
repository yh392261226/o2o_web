<?php
namespace App\Controller;

class Orders extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->orders_dao = new \MDAO\Orders();
        //$this->db->debug = 1;
    }

    public function cStatus()
    {
        $is_ajax = isset($_REQUEST['is_ajax']) ? intval($_REQUEST['is_ajax']) : 0;
        //print_r($_REQUEST);exit;
        if (isset($_REQUEST['o_id']) && intval($_REQUEST['o_id']) > 0)
        {
            $status = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : '';
            if ('' === $status)
            {
                if ($is_ajax)
                {
                    echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
                }
                msg('操作失败:参数错误', 0);
            }

            $result = $this->orders_dao->updateData(array('o_status' => $status), array('o_id' => intval($_REQUEST['o_id'])));

            if (!$result)
            {
                if ($is_ajax)
                {
                    echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
                }
                msg('操作失败', 0);
            }
            if ($is_ajax)
            {
                echo json_encode(array('msg' => '操作成功', 'status' => 1));exit;
            }
            msg('操作成功', 1);
        }
        if ($is_ajax)
        {
            echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
        }
        msg('操作失败', 0);
    }

    //public function del()
    //{
    //    $result = 0;
    //    if (isset($_REQUEST['o_id']))
    //    {
    //        if (is_array($_REQUEST['o_id']) || strpos($_REQUEST['o_id'], ','))
    //        {
    //            $result = $this->orders_dao->delData(array('o_id' => array('type' => 'in', 'value' => $_REQUEST['o_id']))); //伪删除
    //        }
    //        else
    //        {
    //            $result = $this->orders_dao->delData(intval($_REQUEST['o_id'])); //伪删除
    //        }
    //    }
    //    if (!$result) {
    //        //FAILED
    //        msg('操作失败,不允许删除', 0);
    //    }
    //    //SUCCESSFUL
    //    msg('操作成功', 1, '/Msg/list');
    //}
    //
    //public function info()
    //{
    //    $info = array();
    //    if (isset($_REQUEST['o_id']) || isset($_REQUEST['key']))
    //    {
    //        if (isset($_REQUEST['o_id']))
    //        {
    //            $info = $this->orders_dao->infoData(intval($_REQUEST['o_id']));
    //        }
    //        elseif (isset($_REQUEST['key']))
    //        {
    //            $info = $this->orders_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
    //        }
    //    }
    //    $this->tpl->assign('info', $info);
    //    $this->mydisplay();
    //}

    //public function list()
    //{
    //    $list = $data = array();
    //    if (isset($_REQUEST['o_id'])) $data['o_id'] = array('type' => 'in', value => $_REQUEST['o_id']);
    //    if (isset($_REQUEST['t_id'])) $data['t_id'] = intval($_REQUEST['t_id']);
    //    if (isset($_REQUEST['u_id'])) $data['u_id'] = intval($_REQUEST['u_id']);
    //    if (isset($_REQUEST['o_worker'])) $data['o_worker'] = intval($_REQUEST['o_worker']);
    //    if (isset($_REQUEST['o_amount'])) $data['o_amount'] = intval($_REQUEST['o_amount']);
    //    if (isset($_REQUEST['o_status'])) $data['o_status'] = intval($_REQUEST['o_status']);
    //    if (isset($_REQUEST['tew_id'])) $data['tew_id'] = intval($_REQUEST['tew_id']);
    //
    //    if (isset($_REQUEST['o_start_time'])) $data['o_in_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['o_start_time']));
    //    if (isset($_REQUEST['o_end_time'])) $data['o_in_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['o_end_time']));
    //    if (isset($_REQUEST['o_start_time']) && isset($_REQUEST['o_end_time']) && $_REQUEST['o_start_time'] != 0 && $_REQUEST['o_end_time'] != 0 && strtotime($_REQUEST['o_end_time']) < strtotime($_REQUEST['o_start_time']))
    //    {
    //        //结束时间不能小于开始时间
    //        msg('结束时间不能小于开始时间', 0);
    //    }
    //    $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    //
    //    $list = $this->orders_dao->listData($data);
    //    $this->tpl->assign('list', $list);
    //    $this->myPager($list['pager']);
    //    $this->mydisplay();
    //}

}