<?php
namespace WDAO;

class Task_comment extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Task_comment'));
    }

    public function addComment($data = array())
    {
        if (empty($data))
        {
            return false;
        }

        //添加评论
        if (isset($data['t_id']) && isset($data['u_id']) && isset($data['tc_u_id']))
        {
            $curtime = time();
            $param = array(
                't_id' => $data['t_id'],
                'u_id' => $data['u_id'],
                'tc_u_id' => $data['tc_u_id'],
                'tc_in_time' => $curtime,
                'tc_last_edit_time' => $curtime,
                'tc_edit_times' => 1,
                'tc_status' => 1,
                'tc_first_start' => isset($data['start']) ? $data['start'] : 0,
                'tc_start' => isset($data['start']) ? $data['start'] : 0,
            );
            $result = $this->addData($param);
            if (!$result) return false;

            $ext_param = array(
                'tc_id' => $result,
                'tce_desc' => isset($data['desc']) ? trim($data['desc']) : '',
                'tce_img' => isset($data['img']) ? trim($data['img']) : '',
            );
            $ext_result = model('Task_comment_ext')->addData($ext_param);

            $user_result = true;
            //更改被评论人的星级
            if (isset($data['start']) && $data['start'] > 0)
            {
                $user_sql = '';
                switch ($data['start']) {
                    case '3':
                        $user_sql = 'update users set u_high_opinions = u_high_opinions + 1 where u_id = ' . $data['tc_u_id'];
                        break;
                    case '2':
                        $user_sql = 'update users set u_middle_opinions = u_middle_opinions + 1 where u_id = ' . $data['tc_u_id'];
                        break;
                    case '1':
                        $user_sql = 'update users set u_low_opinions = u_low_opinions + 1 where u_id = ' . $data['tc_u_id'];
                        break;
                }
                if ('' != $user_sql) $user_result = model('Users')->queryData($user_sql);
            }

            if ($ext_result && $user_result)
            {
                return true;
            }
        }

        return false;
    }

}