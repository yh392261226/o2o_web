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
            if (!empty($param))
            {
                if (!isset($param['t_id']))
                {
                    $info = $this->listData($param + array('limit' => 1));
                    if (!empty($info) && isset($info[0]['t_id']) && intval($info[0]['t_id']) > 0)
                    {
                        $param['t_id'] = $info[0]['t_id'];
                    }
                    unset($info);
                }

                $result = $this->delData($param);
                if (!$result)
                {
                    return false;
                }
                //删除任务详情
                $info_model = new \App\Model\Task_ext_info();
                $info_model->delData(array('t_id' => $param['t_id']));
                //删除任务相关工人信息
                $worker_model = new \App\Model\Task_ext_worker();
                $worker_model->delData(array('t_id' => $param['t_id']));
                return true;
            }
        }
        return false;
    }
}