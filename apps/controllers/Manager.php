<?php
namespace App\Controller;
/**
* @action   管理员增删改查
* @author   户连超
* @addtime  2017.08.01
* @e-mail	zrkjhlc@gmail.com
*/
class Manager extends \App\AdminBase\AdminBase
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
        $this->managers = new \App\DAO\Manager();  
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
}
