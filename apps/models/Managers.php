<?php 
namespace App\Model;
class Managers extends \MMODEL\ModelBase
{
    public $table   = 'managers';
    public $primary = "m_id";
    
    
    /**
     * 删除管理员(只能按照管理员id删除)
     * {@inheritDoc}
     * @see \App\Model\ModelBase::delData()
     */
    public function delData($data = array())
    {
        if (!empty($data))
        {
            $param['m_id'] = isset($data['m_id']) ? $data['m_id'] : '';
            if (!is_array($data))
            {
                $param['m_id'] = $data;
            }

            if (isset($data['walk']))
            {
                $param['walk'] = $data['walk'];
                unset($data['walk'], $param['m_id']);
            }
            if (empty($param)) return false;
            return $this->updateData(array('m_status' => -2), $param);
        }
        return false;
    }

    //真删除
    //public function delData($data = array())
    //{
    //    return parent::delData($data);
    //}
}