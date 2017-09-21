<?php
/*
 * 广告
 */
namespace App\Controller;

class Advertising extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->advertising_dao = new \WDAO\Advertising();
    }

    public function index()
    {
        $action = (isset($_REQUEST['action']) && '' != trim($_REQUEST['action'])) ? trim($_REQUEST['action']) : 'list';
        if ('' != trim($action))
        {
            $this->$action();
        }
    }

    //列表及搜索
    private function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['a_id'])) $data['a_id'] = array('type' => 'in', 'value' => $_REQUEST['a_id']);
        if (isset($_REQUEST['a_title'])) $data['a_title'] = array('type'=>'like', 'value' => trim($_REQUEST['a_title']));
        if (isset($_REQUEST['a_link'])) $data['a_link'] = trim($_REQUEST['a_link']);
        if (isset($_REQUEST['a_status'])) $data['a_status'] = intval($_REQUEST['a_status']);
        if (isset($_REQUEST['a_type'])) $data['a_type'] = intval($_REQUEST['a_type']);
        if (isset($_REQUEST['r_id'])) $data['r_id'] = intval($_REQUEST['r_id']);
        if (isset($_REQUEST['a_position'])) $data['a_position'] = trim($_REQUEST['a_position']);

        if (isset($_REQUEST['a_start_time'])) $data['a_start_time'] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['a_start_time']));
        if (isset($_REQUEST['a_end_time'])) $data['a_end_time'] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['a_end_time']));

        $data['pager'] = 0;
        $data['order'] = 'a_id desc';
        $list = $this->advertising_dao->listData($data);
        if (!empty($list))
        {
            $this->exportData($list['data']);
        }
        else
        {
            $this->exportData();
        }
    }

    //详情
    private function info()
    {
        $info = array();
        if (isset($_REQUEST['a_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['a_id']))
            {
                $info = $this->advertising_dao->infoData(intval($_REQUEST['a_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->advertising_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }

        if (!empty($info))
        {
            $this->exportData($info);
        }
        else
        {
            $this->exportData();
        }
    }

}