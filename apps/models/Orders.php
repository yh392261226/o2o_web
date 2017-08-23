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
            $status = isset($data['o_status']) intval($data['o_status']) : ''; //-9工人删除 -8雇主删除
            if ('' == $status)
            {
                return false;
            }
            unset($data['o_status']);

            if (is_array($data))
            {
                return $this->updateData(array('o_status' => $status), $data);
            }
            else
            {
                return $this->updateData(array('o_status' => $status), array('o_id' => $data));
            }
        }
        return false;
    }

    public function delData2($data = array())
    {
        return parent::delData($data);
    }
}