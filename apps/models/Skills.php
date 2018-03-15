<?php
namespace App\Model;
class Skills extends \MMODEL\ModelBase
{
    public $table = 'skills';
    public $primary = "s_id";
    protected $allow_delete = false;

 /**
     * 删除技能(只能按照技能id删除)
     * {@inheritDoc}
     * @see \App\Model\ModelBase::delData()
     */
    public function delData($data = array())
    {
        if (!empty($data))
        {
            $param['s_id'] = isset($data['s_id']) ? $data['s_id'] : '';
            if (!is_array($data))
            {
                $param['s_id'] = $data;
            }

            if (isset($data['walk']))
            {
                $param['walk'] = $data['walk'];
                unset($data['walk'], $param['s_id']);
            }
            if (empty($param)) return false;
            return $this->updateData(array('s_status' => 0), $param);
        }
        return false;
    }








}