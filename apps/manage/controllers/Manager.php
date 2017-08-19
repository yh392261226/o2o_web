<?php
namespace App\Controller;

/**
 * @action   管理员增删改查
 * @author   户连超
 * @addtime  2017.08.01
 * @e-mail    zrkjhlc@gmail.com
 */
class Manager extends \CLASSES\AdminBase
{
    protected $managers;
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->managers = new \DAO\Manager();
    }
    /**
     * 管理员列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerList()
    {
        $get_page = !empty($_GET['page']) ? $_GET['page'] : 1;
        $m_name = !empty($_GET['m_name']) ? $_GET['m_name'] : false;
    }
    /**
     * 添加管理员
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerAdd()
    {
        # code...
    }
    /**
     * 修改管理员信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerEdit()
    {
        # code...
    }

    /**
     * 删除管理员信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerDel()
    {

    }/**
     * 权限模块列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesList()
    {
        $data['page'] = !empty($_GET['page']) ? $_GET['page'] : 1;
        !empty($_GET['mpm_name']) ? $data['mpm_name'] = $_GET['mpm_name'] : false;
        !empty($_GET['mpm_status']) ? $data['mpm_status'] = $_GET['mpm_status'] : false;
        /**
         * 查询数据
         * @return array $ret_info
         */
        $ret_info = $this->managers->managerPrivilegesModulesData($data);
        if (false !== $ret_info) {
            $this->tpl->assign("list", $ret_info['data']);
            $this->tpl->assign("page", $ret_info['page']);
        }else{
            $this->tpl->assign("list", '');
            $this->tpl->assign("page", '');
        }
        $this->tpl->display("manager/managerPrivilegesModulesList.html");
    }
    /**
     * 权限模块添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesAdd()
    {
        // $data[0][]= 1;
        // $data[0][] = 1;
        // $data[0][] = 1;

        // $data[1][]= 1;
        // $data[1][] = 1;
        // $data[1][] = 1;

        // $data[2][]= 1;
        // $data[2][] = 1;
        // $data[2][] = 1;
        // $data[3][]= 1;
        // $data[3][] = 1;
        // $data[3][] = 1;
        // $data[4][]= 1;
        // $data[4][] = 1;
        // $data[4][] = 1;
        // $field[] = 'mpm_name';
        // $field[] = 'mpm_value';
        // $field[] = 'mpm_status';
        $ret = $this->managers->managerPrivilegesModulesInsert($data);
        /**
         * 接到数组证明是添加动作
         */
        if ($_POST) {

            $error_jump = '/manager/managerPrivilegesModulesAdd';
            $success_jump = '/manager/managerPrivilegesModulesList';
            
            $data = array();
            (isset($_POST['mpm_name']) && !empty($_POST['mpm_name'])) ? $data['mpm_name'] = $_POST['mpm_name'] : msg("权限模块名不能为空!", $status = 0, $error_jump);
            (isset($_POST['mpm_value']) && !empty($_POST['mpm_value']) && false !== strpos($_POST['mpm_value'], "@")) ? $data['mpm_value'] = encyptController($_POST['mpm_value']) : msg("权限模块英文名填写有误!", $status = 0, $error_jump);
            /**
             * 判断权限状态 如果是on 状态就是 1 开启 什么也没有 状态就是 0 关闭
             */
            (isset($_POST['mpm_status']) && 'on' == $_POST['mpm_status']) ? $data['mpm_status'] = 1 : $data['mpm_status'] = 0;
            /**
             * 描述 放到desc表中
             */
            (isset($_POST['mpm_desc'])) ? $data['mpm_desc'] = $_POST['mpm_desc'] : $data['mpm_desc'] = '';

            $ret = $this->managers->managerPrivilegesModulesInsert($data);
            if (false == $ret) {
                msg("添加失败请重试!!!", $status = 0, $error_jump);
            } else {
                msg("添加成功!", $status = 1, $success_jump);
            }
            $this->http->finish();
        }
        $this->tpl->display("manager/managerPrivilegesModulesAdd.html");
    }
    /**
     * 权限模块修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesEdit()
    {
        # code...
    }
    /**
     * 权限模块删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDel()
    {
        # code...
    }
    /**
     * 管理员分组列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @param  string            $value [description]
     */
    public function managersPrivilegesGroupList()
    {
        # code...
    }
    /**
     * 添加分组
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managersPrivilegesGroupAdd()
    {
        # code...
    }
    /**
     * 修改分组信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managersPrivilegesGroupEdit()
    {
        # code...
    }
    /**
     * 删除分组信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managersPrivilegesGroupDel()
    {
        # code...
    }
}
