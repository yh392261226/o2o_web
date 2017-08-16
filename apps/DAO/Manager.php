<?php
namespace DAO;

/**
 * Class UsManagerer
 * example: $user = new App\DAO\Manager();  $user->get();
 * @package App\DAO
 */
class Manager
{
    // public function show_index($first_page, $m_name, $last_page = 2, $filed = '*')
    // {
    //     $first_page = !empty($first_page) ? $first_page : 1;
    //     $m_name     = !empty($m_name) ? $m_name : false;

    //     if ($m_name) {
    //         $where["m_name"] = $m_name;
    //     } else {
    //         $where = '';
    //     }
    //     $manager          = model('Managers');
    //     $ret_info['list'] = $manager->findAll($where, $filed, $first_page, $last_page);

    //     $ret_info['page'] = $manager->page();
    //     if (!empty($ret_info['list'])) {
    //         $ret_info['page'] = $manager->page();
    //     } else {
    //         $ret_info['page'] = array();
    //     }
    //     // var_dump($where);
    //     // $ret_info = $manager->getAll($where);
    //     // var_dump($ret_info)       ;exit();
    //     return $ret_info;
    // }
    public function showIndex()
    {
        $data['page']            = !empty($first_page) ? $first_page : 1;
        $data['where']['m_name'] = !empty($m_name) ? $m_name : 1;
        $manager                 = model('Managers');
        $ret_info                = $manager->listDatas($data);
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
        $data['m_name']    = deepAddslashes($m_name);
        $data['m_pass']    = encyptPassword($password);
        $data['m_status']  = 0;
        $data['m_in_time'] = time();
        $data['m_inip']    = ip2long(\Swoole::$php->request->getClientIP());
        $data['m_author']  = $_SESSION['m_id'];
        $data['m_last_time'] = 0;
        $data['m_last_ip'] = 0;
        $data['mpg_id']    = $select_role;
        $m_manager         = model('Managers');
        return $m_manager->put($data);
    }
    /* 获取角色列表 */
    public function getRoleList()
    {
        return table('managers_privileges_group')->select('mpg_id, mpg_name, mpm_ids')->fetchall();
    }

    /**
     * 用户登录检测用户名密码是否对应
     * $username 用户名
     * $password 密码
     */

    public function validateManager($username, $password)
    {
        if (!isset($username) || !isset($password) || empty(trim($username)) || empty(trim($username))) {
            return false;
        }
        $username = deepAddslashes(trim($username));
        $data['where'] = array("m_name" => $username, "m_pass" => $password);
        $data['fields'] = 'm_id,m_name,m_status';
        $res    = model("Managers")->infoDatas($data);
        if ($res['m_id'] > 0 && $res['m_status'] >= 0) {
            model("Managers")->
            saveData(
                array(
                    'where' => 'm_id',
                    'm_id' => $res['m_id'],
                    'm_last_time' => time(),
                    'm_last_ip'=>ip2long(\Swoole::$php->request->getClientIP())
                )
            );
            // echo model("Managers")->db->getSql();die;
            return $res;

        } else {

            return false;
        }
    }


    /**
     * @param array $data
     * @return bool
     * @author Ross
     * @desc 删除管理员
     */
    public function delManager($m_id)
    {
        $data = array('m_id'=>$m_id);
        return model("Managers")->delData($data);
    }

    /**
     * 检查用户名是否存在
     * @param  [type]  $name 管理员名称
     * @return  int    大于0存在
     */

    public function hasManagerName($name)
    {
        $where = array('m_name'=>$name);
        $res = model("Managers")->select('m_id')->where($where)->fetch();
        return $res;
    }

}
