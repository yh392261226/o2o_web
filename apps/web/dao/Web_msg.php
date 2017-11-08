<?php
namespace WDAO;

class Web_msg extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Web_msg'));
    }

    /**
     * Web_msg添加消息
     * @param array $data 因为前台的基本上都是订单或任务触发式消息  所以默认type=1
     * @return bool
     */
    public function addWebMsg($data = array())
    {
        if (!empty($data))
        {
            $web_msg_data = array(
                'wm_title'      => (isset($data['title']) && '' != trim($data['title'])) ? trim($data['title']) : '',
                'wm_in_time'    => time(),
                'wm_author'     => (isset($data['author']) && intval($data['author'])) > 0 ? intval($data['author']) : 0,
                'wm_type'       => (isset($data['type']) && intval($data['type'])) > 0 ? intval($data['type']) : 1,
                'wm_status'     => (isset($data['status']) && intval($data['status'])) > 0 ? intval($data['status']) : 0,
                'wm_start_time' => (isset($data['start_time']) && intval($data['start_time'])) > 0 ? intval($data['start_time']) : 0,
                'wm_end_time' => (isset($data['end_time']) && intval($data['end_time'])) > 0 ? intval($data['end_time']) : 0,
                );
            $result = $this->addData($web_msg_data);
            if ($result)
            {
                $web_msg_ext_data = array(
                    'wm_id' => $result,
                    'wm_desc' => (isset($data['desc']) && '' != trim($data['desc'])) ? trim($data['desc']) : '',
                );
                model('Web_msg_ext')->addData($web_msg_ext_data);
                return $result;
            }
        }
        return false;
    }

}