<?php 
namespace App\Model;
class Tasks extends \MMODEL\ModelBase
{
    public $table = 'tasks';
    public $primary = "t_id";

    public function delData($data = array())
    {
        if ($this->allow_delete == false)
        {
            return false;
        }
    }

    //public function delData($data = array())
    //{
    //    if (!empty($data))
    //    {
    //        $param['t_id'] = isset($data['t_id']) ? $data['t_id'] : '';
    //        $status = isset($data['t_status']) intval($data['t_status']) : '-9';
    //        if ('' == $status)
    //        {
    //            return false;
    //        }
    //        unset($data['t_status']);
    //
    //        if (!is_array($data))
    //        {
    //            $param['t_id'] = $data;
    //        }
    //
    //        if (isset($data['walk']))
    //        {
    //            $param['walk'] = $data['walk'];
    //            unset($data['walk'], $param['t_id']);
    //        }
    //        if (empty($param)) return false;
    //        return $this->updateData(array('t_status' => $status), $param);
    //    }
    //    return false;
    //}
}