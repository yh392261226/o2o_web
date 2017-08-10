<?php
namespace DAO;

/**
 * Class UsManagerer
 * example: $user = new App\DAO\Manager();  $user->get();
 * @package App\DAO
 */
class Manager
{
    public function show_index($first_page, $m_name, $last_page = 2, $filed = '*')
    {
        $first_page = !empty($first_page) ? $first_page : 1;
        $m_name     = !empty($m_name) ? $m_name : false;

        if ($m_name) {
            $where["m_name"] = $m_name;
        } else {
            $where = '';
        }
        $manager          = model('Managers');
        $ret_info['list'] = $manager->findAll($where, $filed, $first_page, $last_page);

        $ret_info['page'] = $manager->page();
        if (!empty($ret_info['list'])) {
            $ret_info['page'] = $manager->page();
        } else {
            $ret_info['page'] = array();
        }
        // var_dump($where);
        // $ret_info = $manager->getAll($where);
        // var_dump($ret_info)       ;exit();
        return $ret_info;
    }
    public function edit_manager_info()
    {
        # code...
    }
    /*管理员添加操作*/
    public function managerInsert($m_name, $password, $select_role)
    {
        $data              = array();
        $data['m_name']    = $m_name;
        $data['m_pass']    = $password;
        $data['m_status']  = 0;
        $data['m_in_time'] = time();
        $data['m_inip']    = ip2long($_SERVER["REMOTE_ADDR"]);
        $data['m_author']  = $_SESSION['manager_id'];
        $data['mpg_id']    = $select_role;
        $m_manager         = model('Managers');
        return $m_manager->put($data);
    }
    /* 获取角色列表 */
    public function getRoleList()
    {
        return table('managers_privileges_group')->select('mpg_id, mpg_name, mpm_ids')->fetchall();
    }

}
