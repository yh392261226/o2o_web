<?php
/**
 * 后台管理员类
 *
 *
 *
 *
 *
 */
namespace App\Model;
use Swoole;

class Managers extends App\Model\Model
{
    public $table = 'managers';
    public $primary = "m_id";

    /**
     * @param array $data
     * @return bool
     * @author Ross
     * @desc 删除管理员
     */
    public function delData($data = array())
    {
        if (!empty($data))
        {
            if (!isset($data['m_status'])) $data['m_status'] = -2;
            $this->setdatas($data);
            return $this->set($data[$this->primary], $data, $this->primary);
        }
    }


}