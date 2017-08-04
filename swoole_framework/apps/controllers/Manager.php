<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-01 16:26:47
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-04 10:58:19
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
        //注册信息判断
        if($m_name == "" || $password == "" || $pwd_confirm == "" )
        {
            echo "用户名或密码不能为空";
            die;
        }
        elseif($password !== $pwd_confirm)
        {
            echo "两次输入的密码不一致";
            die;
        }
        elseif($select_role == 0 )
        {
            echo "请选择角色";
            die;
        }
        if(!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $m_name))
        {
            exit('错误：用户名不符合规定.');
        }
        if(strlen($password) < 6)
        {
            exit('错误：密码长度不符合规定.');
        }

        $password = $this->enctypePass($password);
        $manager = model('Manager');
        //检查管理员名是否存在
        $res = $manager->has_manager_name($m_name);
        if($res['m_id'])
        {
            echo "管理员名已经存在,请更换管理员名称";die;
        }
        $manager->manager_insert($m_name,$password,$select_role);
    }

    //删除管理员
    public function del()
    {
        $m_id = intval(trim($_GET['m_id']));
        if($m_id > 0){
            $res = model('Manager')->del_manager($m_id);
            if($res){
                echo "管理员删除成功";die;
            }else{
                echo "管理员删除失败";die;
            }
        }else{
            echo "你传入的数据有误";die;
        }
    }


}