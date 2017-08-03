<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-01 16:26:47
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-02 15:23:09
 */
namespace App\Controller;
use Swoole;
class Manager extends \App\AdminBase\AdminBase
{
    // 添加加管理员页面
    public function add()
    {
        $role_list = model('Manager')->get_role_list();
        $this->tpl->assign('role_list', $role_list);
        $this->tpl->display('manager/add.php');
    }
    //添加管理员数据库操作
    public function insert()
    {
        $m_name = trim($_POST['manager_name']);
        $password = trim($_POST['password']);
        $pwd_confirm = trim($_POST['pwd_confirm']);
        $select_role = intval(trim($_POST['select_role']));
        if($m_name == "" || $password == "" || $pwd_confirm == "" )
        {
            echo "用户名或密码不能为空";
            die;
        }elseif($password !== $pwd_confirm)
        {
            echo "两次输入的密码不一致";
            die;
        }elseif($select_role == 0 ){
            echo "请选择角色";
            die;
        }
        $password = $this->enctypePass($password);
        $manager = model('Manager');
        $manager->manager_insert($m_name,$password,$select_role);


    }



}