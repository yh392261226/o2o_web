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
    public function delData($data = array(), $params = array(), $type = 0)
    {
        if (!empty($data) && !empty($params))
        {
            if (intval($type) == 0) //更新单条
            {
                $where = isset($params['where']) ? $params['where'] : '';
                return $this->set($params['id'], $data, $where);
            }
            return $this->sets($data, $params);
        }
        return false;
    }
}
