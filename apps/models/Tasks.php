<?php 
namespace App\Model;
class Task_comment_ext extends \MMODEL\ModelBase
{
    public $table = 'tasks';
    public $primary = "t_id";

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
                return $this->updateData(array('t_status' => $status), $data);
            }
            else
            {
                return $this->updateData(array('t_status' => $status), array('t_id' => $data));
            }
        }
        return false;
    }

    public function delData2($data = array())
    {
        return parent::delData($data);
    }
}