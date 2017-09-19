<?php
/**
 * 地区接口
 */
namespace App\Controller;

class Regions extends \CLASSES\WebBase
{
    public $data = array();

    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->regions_dao = new \WDAO\Regions();
    }

    public function index()
    {
        $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : 'list';
        if ('' != trim($action))
        {
            $this->$action();
        }
    }

    //列表及搜索
    private function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['r_id'])) $data['r_id'] = array('type' => 'in', 'value' => $_REQUEST['r_id']);
        if (isset($_REQUEST['r_pid'])) $data['r_pid'] = $_REQUEST['r_pid'];
        if (isset($_REQUEST['r_shortname'])) $data['r_shortname'] = trim($_REQUEST['r_shortname']);
        if (isset($_REQUEST['r_name'])) $data['r_name'] = array('type'=>'like', 'value' => trim($_REQUEST['r_name']));
        if (isset($_REQUEST['r_status'])) $data['r_status'] = intval($_REQUEST['r_status']);
        if (isset($_REQUEST['r_first'])) $data['r_first'] = $_REQUEST['r_first'];
        if (isset($_REQUEST['mpg_id'])) $data['mpg_id'] = intval($_REQUEST['mpg_id']);
        $data['pager'] = 0;
        $data['order'] = 'r_id asc';
        $list = $this->regions_dao->listData($data);
        if (!empty($list))
        {
            $this->exportData($list['data']);
        }
        else
        {
            $this->exportData();
        }
    }

    private function hot()
    {
        $list = $data = array();
        $data['pager'] = 0;
        $data['r_hot'] = 1;
        $data['order'] = 'r_id asc';
        $list = $this->regions_dao->listData($data);
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

        if (!empty($info))
        {
            $this->exportData($info);
        }
        else
        {
            $this->exportData();
        }
    }

    //获取当前地区id的子集
    private function son()
    {
        $r_id = isset($_REQUEST['r_id']) ? intval($_REQUEST['r_id']) : 0;
        $loop = isset($_REQUEST['loop']) ? $_REQUEST['loop'] : false; //如果loop为真 则获取子集及子集的子集  否则获取一级子集

        $this->data = $this->_regions();
        $tree = $result = array();

        if (!empty($this->data))
        {
            $tree = $this->_createTree(0);
            if (!empty($tree))
            {
                $result = searchKeyFromRegions($r_id, $tree);
                if (!$loop)
                {
                    if (isset($result['sub']) && is_array($result['sub']))
                    {
                        foreach ($result['sub'] as $key => $val)
                        {
                            unset($result['sub'][$key]['sub']);
                        }
                    }
                }
            }
        }
        $this->exportData($result);
    }

    //获取当前地区的父级
    private function parent()
    {

    }

    //首字母开头的城市列表 没有省 没有县、区、镇
    private function letter()
    {
        $this->data = $this->_regions();
        $tree = $result = array();
        if (!empty($this->data))
        {
            $tree = $this->_createTree(1);
            if (!empty($tree))
            {
                foreach ($tree as $key => $val)
                {
                    if (isset($val['sub']) && is_array($val['sub']))
                    {
                        foreach ($val['sub'] as $k => $v)
                        {
                            if (isset($v['sub']))
                            {
                                unset($v['sub']);
                            }
                            $result[$v['r_first']][] = $v;
                        }
                    }
                }
            }
            ksort($result);
        }
        $this->exportData($result);
    }

    //所有地区信息
    private function _regions()
    {
        $list = $data = array();
        $data['pager'] = 0;
        $data['order'] = 'r_id asc';
        $list = $this->regions_dao->listData($data);
        if (!empty($list))
        {
            return $list['data'];
        }
        return array();
    }
    //获取子集
    private function _getSons(&$data, $id, $parent_key = 'r_pid')
    {
        $sons = array();
        if (!empty($data) && $id > -1)
        {
            foreach($data as $k=>$v)
            {
                if ($v['r_pid'] == $id)
                {
                    $sons[$k] = $v;
                }
            }
        }
        return $sons;
    }
    //构建树
    private function _createTree($topid='0', $primary_key = 'r_id') {
        if (!empty($this->data))
        {
            $sons = $this->_getSons($this->data, $topid);
            if (!empty($sons))
            {
                foreach($sons as $k=>$v)
                {
                    $result=$this->_createTree($v[$primary_key]);
                    if (null != $result)
                    {
                        $sons[$k]['sub'] = $result;
                    }
                }
            }
            else
            {
                return null;
            }
        }
        else
        {
            return array();
        }
        return $sons;
    }


}