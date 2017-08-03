<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-01 16:53:04
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-02 16:48:30
 */
namespace App\Model;
use Swoole;

class Manager extends Swoole\Model
{
    /* 获取角色列表 */
    public function get_role_list()
    {
        $list = array();

        $list = $this->db->query("SELECT mpg_id, mpg_name, mpm_ids FROM managers_privileges_group")->fetchall();
        return $list;
    }
    /*管理员添加操作*/
    public function manager_insert($m_name,$password,$select_role)
    {
        $data = array();
        $data['m_name'] = $m_name;
        $data['m_pass'] = $password;
        $data['m_status'] = 0;
        $data['m_in_time'] = time();
        $data['m_inip'] = $_SERVER["REMOTE_ADDR"];
        $data['m_author'] = $_SESSION['manager_id'];
        $data['mpg_id'] = $select_role;
        if($this->db->insert($data,'managers')){
            echo "管理员添加成功";
        }else{
            echo "管理员添加失败";
        }


    }
}