<?php 
namespace App\Model;
class Orders extends \MMODEL\ModelBase
{
    public $table = 'orders';
    public $primary = "o_id";

    /**
     * 删除订单(只能按照id删除)
     * {@inheritDoc}
     * @see \App\Model\ModelBase::delData()
     */
    public function delData($data = array())
    {
        if (!empty($data))
        {
            $param['o_id'] = isset($data['o_id']) ? $data['o_id'] : '';
            $status = isset($data['o_status']) ? intval($data['o_status']) : ''; //-9工人删除 -8雇主删除
            if ('' == $status)
            {
                return false;
            }
            unset($data['o_status']);

            if (!is_array($data))
            {
                $param['o_id'] = $data;
            }

            if (isset($data['walk']))
            {
                $param['walk'] = $data['walk'];
                unset($data['walk'], $param['o_id']);
            }
            if (empty($param)) return false;
            return $this->updateData(array('o_status' => $status), $param);
        }
        return false;
    }

    public function realDel($data = array())
    {
        if (!empty($data))
        {
            return parent::delData($data);
        }
        return false;
    }
}