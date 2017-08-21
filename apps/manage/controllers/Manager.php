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

    }
    /**
     * 权限模块列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesList()
    {
        $data = array();
        (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $data['page'] = $_REQUEST['page'] : $data['page'] = 1;
        (isset($_POST['mpm_name']) && !empty($_POST['mpm_name'])) ? ($data['mpm_name'] = $_POST['mpm_name']) : false;
        (isset($_POST['mpm_status']) && $_POST['mpm_status'] >= "0") ? ($data['mpm_status'] = $_POST['mpm_status']) : false;
        /**
         * 查询数据
         * @return array $ret_info
         */
        $ret_info = $this->managers->managerPrivilegesModulesData($data);

        if (false !== $ret_info) {
            $this->tpl->assign("list", $ret_info['data']);
            $this->tpl->assign("page", $ret_info['page']);
        } else {
            $this->tpl->assign("list", '');
            $this->tpl->assign("page", '');
        }
        if (empty($data)) {
            $data["mpm_status"] = '';
            $data['mpm_name'] = '';
        }
        $this->tpl->assign("data", $data);
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
        /**
         * 接到数组证明是添加动作
         */
        if (isset($_POST) && !empty($_POST)) {

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
        if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
            $id = $_REQUEST['id'];
            $mpm_info = $this->managers->managerPrivilegesModuleInfo($id);
        }

        if (isset($_POST) && !empty($_POST)) {
            $error_jump = '/manager/managerPrivilegesModulesEdit?id=' . $id;
            $success_jump = '/manager/managerPrivilegesModulesList';

            $data = array();

            (isset($_POST['mpm_name']) && !empty($_POST['mpm_name'])) ? $data['mpm_name'] = $_POST['mpm_name'] : msg("权限模块名不能为空!", $status = 0, $error_jump);

            if (isset($_POST['mpm_value']) && !empty($_POST['mpm_value'])) {
                if ($_POST['mpm_value'] == $mpm_info['data'][0]['mpm_value']) {
                    $data['mpm_value'] = $_POST['mpm_value'];
                } elseif (false !== strpos($_POST['mpm_value'], "@")) {
                    msg("权限模块英文名填写有误!", $status = 0, $error_jump);
                } else {
                    $data['mpm_value'] = encyptController($_POST['mpm_value']);
                }
            }
            (isset($_POST['mpm_status']) && 'on' == $_POST['mpm_status']) ? $data['mpm_status'] = 1 : $data['mpm_status'] = 0;
            (isset($_POST['mpm_desc'])) ? $data['mpm_desc'] = $_POST['mpm_desc'] : $data['mpm_desc'] = '';
            (isset($_POST['id'])) ? $info['id'] = $_POST['id'] : msg("数据传输错误,请重试!", $status = 0, $error_jump);

            $ret_info = $this->managers->managerPrivilegesModulesUpdate($info, $data);
            if (false === $ret_info) {
                msg("修改失败,请重试!", $status = 0, $error_jump);
            } else {
                msg("修改成功!", $status = 1, $success_jump);
            }
            $this->http->finish();
        }

        if (isset($mpm_info['data'][0]) && $mpm_info['data'][0]) {
            $this->tpl->assign("mpm_info", $mpm_info['data'][0]);
        } else {
            $this->tpl->assign("mpm_info", "");
        }
        $this->tpl->display("manager/managerPrivilegesModulesEdit.html");
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
        $error_jump = '/manager/managerPrivilegesModulesList';
        $success_jump = '/manager/managerPrivilegesModulesList';
        (isset($_GET['id']) && !empty($_GET['id'])) ? $id = $_GET['id'] : false;
        $ret_info = $this->managers->managerPrivilegesModulesDelete($id);
        if ($ret_info) {
            msg("删除成功!", $status = 1, $success_jump);
        } else {
            msg("删除失败,请重试!", $status = 0, $error_jump);
        }
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
        $data = array();
        (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $data['page'] = $_REQUEST['page'] : $data['page'] = 1;
        (isset($_POST['mpg_name']) && !empty($_POST['mpg_name'])) ? ($data['mpg_name'] = $_POST['mpg_name']) : false;
        (isset($_POST['mpg_status']) && $_POST['mpg_status'] >= -1) ? ($data['mpg_status'] = $_POST['mpg_status']) : false;
        (isset($_POST['mpg_author']) && !empty($_POST['mpg_author'])) ? ($data['mpg_author'] = $_POST['mpg_author']) : false;
        (isset($_POST['mpg_editor']) && !empty($_POST['mpg_editor'])) ? ($data['mpg_editor'] = $_POST['mpg_editor']) : false;
        /**
         * 查询数据
         * @return array $ret_info
         */
        $ret_info = $this->managers->managersPrivilegesGroupData($data);

        echo "<pre>";
        var_dump($ret_info);
        if (false !== $ret_info) {
            $this->tpl->assign("list", $ret_info['data']);
            $this->tpl->assign("page", $ret_info['page']);
        } else {
            $this->tpl->assign("list", '');
            $this->tpl->assign("page", '');
        }
        if (empty($data)) {
            $data["mpm_status"] = '';
            $data['mpm_name'] = '';
        }
        $this->tpl->assign("data", $data);
        $this->tpl->display("manager/managersPrivilegesGroupList.html");
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
        if (isset($_POST) && !empty($_POST)) {
            $error_jump = '/manager/managersPrivilegesGroupAdd';
            $success_jump = '/manager/managersPrivilegesGroupList';

            $data = array();
            (isset($_POST['mpg_name']) && !empty($_POST['mpg_name'])) ? $data['mpg_name'] = $_POST['mpg_name'] : msg("分组名不能为空!", $status = 0, $error_jump);
            (isset($_POST['mpg_status']) && !empty($_POST['mpg_status'])) ? $data['mpg_status'] = $_POST['mpg_status'] : false;
            (isset($_POST['mpm_ids']) && !empty($_POST['mpm_ids'])) ? $data['mpm_ids'] = implode(",", $_POST['mpm_ids']) : false;
            $data['mpg_author'] = $_SESSION['m_id'];
            $data['mpg_editor'] = $_SESSION['m_id'];
            $ret = $this->managers->managersPrivilegesGroupInsert($data);
            if (false == $ret) {
                msg("添加失败请重试!!!", $status = 0, $error_jump);
            } else {
                msg("添加成功!", $status = 1, $success_jump);
            }
            $this->http->finish();
        }
        $mpm_list = $this->managers->managerPrivilegesModulesData('', 1);
        $this->tpl->assign("mpm_list", $mpm_list["data"]);
        $this->tpl->display("manager/managersPrivilegesGroupAdd.html");
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
