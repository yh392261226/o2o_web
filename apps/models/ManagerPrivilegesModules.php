<?php
namespace App\Model;

class ManagerPrivilegesModules extends \CLASSES\ModelBase
{
    public $table   = 'manager_privileges_modules';
    public $primary = "mpm_id";

    /**
     * @param array $data
     * @return bool
     * @author Ross
     * @desc 删除管理员
     */
    public function delData($data = array(),$type=0)
    {
        if (!empty($data)) {
            if (!isset($data['mpm_status'])) {
                $data['mpm_status'] = 0;
            }
            return $this->set($data[$this->primary], $data, $this->primary);
        }
    }
}
