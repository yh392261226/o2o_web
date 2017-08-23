<?php 
namespace App\Model;
class Users extends \MMODEL\ModelBase
{
    public $table = 'users';
    public $primary = "u_id";

    /**
     * 删除用户(只能按照id删除)
     * {@inheritDoc}
     * @see \App\Model\ModelBase::delData()
     */
    public function delData($data = array())
    {
        if (!empty($data))
        {
            $status = -9;
            if (is_array($data))
            {
                return $this->updateData(array('u_status' => $status), $data);
            }
            else
            {
                return $this->updateData(array('u_status' => $status), array('u_id' => $data));
            }
        }
        return false;
    }

    public function delData2($data = array())
    {
        return parent::delData($data);
    }
}