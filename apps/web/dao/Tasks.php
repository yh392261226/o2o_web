<?php
namespace WDAO;

class Tasks extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Tasks'));
    }

    //删除任务及任务详情与任务工人
    public function delOldTask($data)
    {
        if (!empty($data))
        {
            $param = array();
            if (isset($data['t_id']) && intval($data['t_id']) > 0) $param['t_id'] = intval($data['t_id']);
            if (isset($data['t_author']) && intval($data['t_author']) > 0) $param['t_author'] = intval($data['t_author']);
            if (isset($data['t_storage']) && intval($data['t_storage']) > 0) $param['t_storage'] = intval($data['t_storage']);

            if (!empty($param) && (isset($param['t_id']) || isset($param['t_author'])))
            {
                if (!isset($param['t_id']))
                {
                    $param['pager'] = 0;
                    $list = $this->listData($param);
                    if (!empty($list['data']))
                    {
                        foreach ($list['data'] as $key => $val)
                        {
                            $param['t_id'][] = $val['t_id'];
                        }
                    }
                    unset($list);
                }

                $result = $this->delData(array('t_id' => array('value' => $param['t_id'], 'type' => 'in')));
                if (!$result)
                {
                    return false;
                }
                //删除任务详情
                $info_model = model('Task_ext_info');
                $info_model->delData(array('t_id' => array('value' => $param['t_id'], 'type' => 'in')));
                //删除任务相关工人信息
                $worker_model = model('Task_ext_worker');
                $worker_model->delData(array('t_id' => array('value' => $param['t_id'], 'type' => 'in')));
                return true;
            }
        }
        return false;
    }
}