<?php
namespace App\Controller;
class Tools extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }

    public function index()
    {
        $data['datetime'] = array(
            'timestamp' => time(),
            'date'      => date('Y-m-d'), //2009-10-10
            'his'       => date('H:i:s'), //22:10:10
        );
        $this->exportData($data);
    }

    public function subTotal()
    {
        $result = 0;
        if (!empty($_REQUEST['data']))
        {
            $request_data = json_decode(base64_decode($_REQUEST['data']), true);
            $worker = array();
            foreach ($request_data['worker'] as $key => $val)
            {
                $worker[$key][0] = isset($val['personNum']) ? intval($val['personNum']) : 0;
                $worker[$key][1] = isset($val['money']) ? floatval($val['money']) : 0;
                $worker[$key][2] = isset($val['startTime']) ? strtotime($val['startTime']) : 0;
                $worker[$key][3] = isset($val['endTime']) ? strtotime($val['endTime']) : 0;
                $result += $worker[$key][0] * $worker[$key][1] * (ceil($worker[$key][3] - $worker[$key][2]) / 3600 / 24 + 1);
            }
        }
        $this->exportData($result);
    }

    /**
     * 任务类型
     */
    public function taskType()
    {
        $types = array(
            0 => '家装',
            1 => '工装',
            2 => '工地施工',
        );
        $this->exportData($types);
    }

}
