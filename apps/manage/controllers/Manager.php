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
    }
	public function index()
	{
        $this->db->debug = true;

        $get_page = !empty($_GET['page']) ? $_GET['page'] : 1;
        $m_name= !empty($_GET['m_name']) ? $_GET['m_name'] : false;
        $this->managers = new \DAO\Manager();
        $info = $this->managers->show_index($get_page,$m_name);

        $this->tpl->assign("list",$info['list']);
        $this->tpl->assign("page",$info['page']);
        $this->tpl->display("manager/index.html");
	}
	public function edit_manager_info()
	{
        // $table  = table('managers');
        $table  = Model('Managers');
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
        $this->managers = new \DAO\Manager();
        $role_list = $this->managers->getRoleList();
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
        if(empty($m_name) || empty($password)  || empty($pwd_confirm))
        {
            $message = '用户名或密码不能为空';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }
        elseif($password !== $pwd_confirm)
        {
            $message = '两次输入的密码不一致';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }
        elseif($select_role == 0 )
        {
            $message = '请选择角色';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }
        if(!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $m_name))
        {
            $message = '用户名不符合规定!';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }
        if(strlen($password) < 6)
        {
            $message = '密码长度不符合规定!';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }

        $password = $this->encrypt($password);
        $manager = model('Managers');
        //检查管理员名是否存在
        $res = $manager->hasManagerName($m_name);
        if($res['m_id'])
        {
            $message = '管理员名已经存在,请更换管理员名称！';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }
        $this->managers = new \DAO\Manager();
        $res = $this->managers->managerInsert($m_name,$password,$select_role);
        if($res){
            $message = '会员添加成功！';
            $this->show_msg($message,$status=1);
            exit;
        }else{
            $message = '会员添加失败！';
            $jumpUrl = '/manager/add';
            $this->show_msg($message,$status=1,$jumpUrl);
            exit;
        }
    }

    //删除管理员
    public function del()
    {
        $m_id = intval(trim($_GET['m_id']));
        if($m_id > 0){
            $res = model('Managers')->delManager($m_id);
            if($res){
                $message = '管理员删除成功!';
                $jumpUrl = '/manager/index';
                $this->show_msg($message,$status=1,$jumpUrl);
                exit;
            }else{
                $message = '管理员删除失败!';
                $jumpUrl = '/manager/index';
                $this->show_msg($message,$status=0,$jumpUrl);
                exit;
            }
        }else{
            $message = '你传入的数据有误!';
            $jumpUrl = '/manager/index';
            $this->show_msg($message,$status=0,$jumpUrl);
            exit;
        }
    }
}
