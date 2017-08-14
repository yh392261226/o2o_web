<?php
namespace App\Model;

// use Swoole;

class Managers extends \CLASSES\ModelBase
{
    //    public $table = 'managers';
    //    public $primary = "m_id";
    // protected $where = "1";
    // protected $field = '*';
    // protected $first_page = 0;
    // protected $last_page = 30;

    /**
     * 测试开始
     */

    public $table   = 'managers';
    public $primary = "m_id";

    /**
     * @param array $data
     * @return bool
     * @author Ross
     * @desc 删除管理员
     */
    public function delData($data = array(),$type=0)
    {
        if (!empty($data)) {
            if (!isset($data['m_status'])) {
                $data['m_status'] = -2;
            }
            return $this->set($data[$this->primary], $data, $this->primary);
        }
    }
}
