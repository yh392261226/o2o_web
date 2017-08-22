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
            if (is_array($data))
            {
                return $this->updateData(array('m_status' => -2), $data);
            }
            else
            {
                return $this->updateData(array('m_status' => -2), array('m_id' => $data));
            }
        }
        return false;
    }

    public function delData2($data = array())
    {
        return parent::delData($data);
    }
}