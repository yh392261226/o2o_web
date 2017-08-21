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
            return $this->updateData(array('m_status' => -2), $data);
        }
    }
}