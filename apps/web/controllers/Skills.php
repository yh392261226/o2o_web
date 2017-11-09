<?php
/**
 * 工种接口
 */
namespace App\Controller;

class Skills extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->skills_dao = new \WDAO\Skills();
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
        if (isset($_REQUEST['s_id'])) $data['s_id'] = array('type' => 'in', 'value' => $_REQUEST['s_id']);
        if (isset($_REQUEST['s_name'])) $data['s_name'] = array('type'=>'like', 'value' => trim($_REQUEST['s_name']));
        $data['s_status'] = 1;
        if (isset($_REQUEST['s_status'])) $data['s_status'] = intval($_REQUEST['s_status']);
        $data['pager'] = 0;
        $data['order'] = 's_id asc';
        $list = $this->skills_dao->listData($data);
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
        if (isset($_REQUEST['s_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['s_id']))
            {
                $info = $this->skills_dao->infoData(intval($_REQUEST['s_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->skills_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
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