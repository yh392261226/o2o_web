<?php
namespace App\Controller;
/**
* @action   管理员增删改查
* @author   户连超
* @addtime  2017.08.01
* @e-mail	zrkjhlc@gmail.com
*/
class Manager extends \CLASSES\AdminBase
{
    protected $managers;
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->managers = new \DAO\Manager();
    }
	public function index()
	{
        $this->db->debug = true;

        $get_page = !empty($_GET['page']) ? $_GET['page'] : 1;
        $m_name= !empty($_GET['m_name']) ? $_GET['m_name'] : false;
       
        $info = $this->managers->show_index($get_page,$m_name);

        $this->tpl->assign("list",$info['list']);
        $this->tpl->assign("page",$info['page']);
        $this->tpl->display("manager/index.html");
	}
	public function edit_manager_info()
	{
        // $this->managers->edit_manager_info()
        $m_id = $_GET['m_id'] = 1;
        $manager_info = $table->get($m_id);
        $managers_privileges_group_list = table('managers_privileges_group')->select('*')->where(array("mpg_status" => 2))->fetchall();
        if ($_POST) {
            // var_dump($_POST);exit;
            if ($_POST['m_pass'] !== $_POST['m_pass_check']) {
                $message = "两次输入密码不一致！！！";
                $this->show_msg($message,$status=0);
            }else{
                if ($manager_info['m_pass'] == $_POST['m_pass']) {
                    unset($_POST['m_pass']);
                }else{
                    $_POST['m_pass'] = $this->encrypt($_POST['m_pass']);
                }
                if ($_POST['mpg_id'] == '') {
                    unset($_POST['mpg_id']);
                }
                unset($_POST['m_pass_check']);
            }
            $status = $table->set($m_id,$_POST);
            if ($status == 1) {
                $message = '会员信息修改成功！';
                $jumpUrl = '/manager/index';
                $this->show_msg($message,$status=1,$jumpUrl);
                exit;
            }
        }
        if ($manager_info) {
            $this->tpl->assign("manager",$manager_info);
        }
        if ($managers_privileges_group_list) {
            $this->tpl->assign("mpg_list",$managers_privileges_group);
        }
		$this->tpl->display('manager/edit_manager_info.html');
	}

    // 添加加管理员页面
    public function add()
    {
        $role_list = model('Managers')->get_role_list();
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
        $manager = model('Managers');
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
            $res = model('Managers')->del_manager($m_id);
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
