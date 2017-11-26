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
            $param['u_id'] = isset($data['u_id']) ? $data['u_id'] : '';
            if (!is_array($data))
            {
                $param['u_id'] = $data;
            }

            if (isset($data['walk']))
            {
                $param['walk'] = $data['walk'];
                unset($data['walk'], $param['u_id']);
            }
            if (empty($param)) return false;
            return $this->updateData(array('u_status' => -9), $param);
        }
        return false;
    }
}