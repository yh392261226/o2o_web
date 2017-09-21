<?php
/**
 * 支付接口
 */
namespace App\Controller;

class Payments extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->payments_dao = new \WDAO\Payments();
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
        if (isset($_REQUEST['p_id'])) $data['p_id'] = array('type' => 'in', 'value' => $_REQUEST['p_id']);
        if (isset($_REQUEST['p_name'])) $data['p_name'] = array('type'=>'like', 'value' => trim($_REQUEST['p_name']));
        if (isset($_REQUEST['p_status'])) $data['p_status'] = intval($_REQUEST['p_status']);
        if (isset($_REQUEST['p_type'])) $data['p_type'] = intval($_REQUEST['p_type']);
        if (isset($_REQUEST['p_default'])) $data['p_default'] = intval($_REQUEST['p_default']);
        if (isset($_REQUEST['p_author'])) $data['p_author'] = trim($_REQUEST['p_author']);
        $data['pager'] = 0;
        $data['order'] = 'p_id asc';
        $list = $this->payments_dao->listData($data);
        if (!empty($list['data']))
        {
            foreach ($list['data'] as $key => $val)
            {
                if (isset($val['p_paras']) && '' != trim($val['p_paras']))
                {
                    //unset($list['data'][$key]['p_paras']);
                    $list['data'][$key]['p_paras'] = @unserialize($val['p_paras']);
                }
            }
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

        if (!empty($info))
        {
            if (isset($info['p_paras']) && '' != trim($info['p_paras'])) $info['p_paras'] = unserialize($info['p_paras']);
            $this->exportData($info);
        }
        else
        {
            $this->exportData();
        }
    }

}