<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-01 16:53:04
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-04 09:25:10
 */
namespace App\Model;
use Swoole;

class Manager extends Swoole\Model
{
    public $table = 'managers';
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
        $data['m_inip'] =  ip2long($_SERVER["REMOTE_ADDR"]);
        $data['m_author'] = $_SESSION['manager_id'];
        $data['mpg_id'] = $select_role;
        if($this->db->insert($data,'managers')){
            echo "管理员添加成功";
        }else{
            echo "管理员添加失败";
        }


    }

    /**
     * 检查用户名是否存在
     * @param  [type]  $name 管理员名称
     * @return  int    大于0存在
     */

    public function has_manager_name($name)
    {
        $where = array('m_name'=>$name);
        $res = $this->select('m_id')->where($where)->fetch();
        return $res;
    }

    /**
     * [del_manager description]删除管理员
     * @param  [type] $m_id 管理员id
     * @return [type] bool
     */

    public function del_manager($m_id)
    {
        $data = array('m_status'=>'-2');
        $res = $this->set($m_id,$data,'m_id');
        return $res;
    }
}