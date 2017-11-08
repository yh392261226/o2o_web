<?php
namespace WDAO;

class User_msg extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'User_msg'));
    }

    /**
     * 增加用户站内信
     * @param array $data
     * @return bool
     */
    public function addUserMsg($data = array())
    {
        if (!empty($data) && isset($data['u_id']) && intval($data['u_id']) > 0 &&
            isset($data['wm_id']) && intval($data['wm_id']) > 0 &&
            isset($data['from_id']) && intval($data['from_id']) > 0)
        {
            $curtime = time();
            $user_msg_data = array(
                'u_id' => $data['u_id'],
                'wm_id'=> $data['wm_id'],
                'from_id' => $data['from_id'],
                'um_in_time' => $curtime,
                'um_status' => 0,
                'um_last_edit_time' => $curtime,
            );
            return $this->addData($user_msg_data);
        }
        return false;
    }

}