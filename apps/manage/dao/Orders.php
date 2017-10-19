<?php
namespace MDAO;

class Orders extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Orders'));
    }

    public function updateStatus($data = array(), $param = array())
    {
        if (!empty($data) && !empty($param))
        {
            $result = $this->updateData($data, $param);
            if (isset($data['tewo_status']) && in_array($data['tewo_status'], array(-3, 2)))
            {
                if ($data['tewo_status'] == -3) $status = 2;
                if ($data['tewo_status'] == 2) $status = 3;
                model('Task_ext_worker_order')->updateData(array('tewo_status' => $status), array('o_id' => $param['o_id']));
            }
            return $result;
        }
        return false;
    }
}