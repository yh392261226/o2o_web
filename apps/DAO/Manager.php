<?php
namespace DAO;

/**
 * @action   后台管理员数据操作(包含登录,登出)
 * @author   户连超
 * @addtime  2017.08.14
 * @e-mail    zrkjhlc@gmail.com
 */
class Manager
{
    /**
     * 管理员列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerData($data = array(), $type = 0)
    {
        $info = array();
        /**
         * 分页
         */
        (isset($data['page']) && !empty($data['page'])) ? $info['page'] = $data['page'] : false;
        /**
         * where语句
         */
        (isset($data['m_name']) && !empty($data['m_name'])) ? $info['walk']['where']["like"] = array("m_name", "%" . $data['m_name'] . "%") : false;
        (isset($data['m_status']) && $data['m_status'] >= "-2") ? $info['m_status'] = $data['m_status'] : false;

        $mpm_data = model("Managers")->listDatas($info, $type);
        $mpm_data = deepStripslashes($mpm_data);
        if (!empty($mpm_data)) {
            return $mpm_data;
        } else {
            return false;
        }
    }
    /**
     * 添加管理员
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerInsert()
    {
        # code...
    }
    public function managerInfo($id)
    {
        $data['fields'] = "*";
        $data['where']['m_id'] = $id;
        return model("Managers")->infoDatas($data);
    }
    /**
     * 修改管理员信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerUpdate()
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
    public function managerDelete($m_id = '')
    {
        $data = array('m_id' => $m_id);
        return model("Managers")->delData($data);
    }
    /**
     * 权限模块列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesData($data = array(), $type = 0)
    {
        $info = array();
        /**
         * 分页
         */
        (isset($data['page']) && !empty($data['page'])) ? $info['page'] = $data['page'] : false;
        /**
         * where语句
         */
        (isset($data['mpm_name']) && !empty($data['mpm_name'])) ? $info['walk']['where']["like"] = array("mpm_name", "%" . $data['mpm_name'] . "%") : false;
        (isset($data['mpm_status']) && $data['mpm_status'] >= "0") ? $info['mpm_status'] = $data['mpm_status'] : false;
        /**
         * leftjoin 数组 两值  第一个表, 第二个关联的内容
         */
        $info['leftjoin'] = array('manager_privileges_modules_desc', "manager_privileges_modules.mpm_id = manager_privileges_modules_desc.mpm_id");
        // echo "<pre>";
        // var_dump($info);
        $mpm_data = model("ManagerPrivilegesModules")->listDatas($info, $type);
        $mpm_data = deepStripslashes($mpm_data);
        if (!empty($mpm_data)) {
            return $mpm_data;
        } else {
            return false;
        }
    }
    /**
     * 权限模块添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesInsert($data = array())
    {
        /**
         * 如果传输过来的数据是空, 出错
         */
        if (empty($data)) {
            return false;
            $this->http->finish();
        }
        /**
         * 处理完的数据是空, 出错
         */
        $data = deepAddslashes($data);
        if (!is_array($data)) {
            return false;
            $this->http->finish();
        }
        $mpm = model("ManagerPrivilegesModules");
        /**
         * 如果有描述的话 把描述添加进去
         */
        if (isset($data['mpm_desc']) && !empty($data['mpm_desc'])) {
            $desc['mpm_desc'] = $data['mpm_desc'];
        }
        unset($data['mpm_desc']);
        $mpm_ret = $mpm->addData($data);
        /**
         * 获取刚添加数据的id
         */
        $desc['mpm_id'] = $mpm_ret;
        model("ManagerPrivilegesModulesDesc")->addData($desc);

        return $mpm_ret;
    }
    /**
     * 权限模块修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesUpdate($info = array(), $data = array())
    {
        if (isset($info) && !empty($info['id']) && !empty($data)) {
            $data = deepAddslashes($data);
            $desc['mpm_desc'] = $data['mpm_desc'];
            unset($data['mpm_desc']);
            $ret_info[] = model('ManagerPrivilegesModules')->updateData($data, $info);
            $ret_info[] = model("ManagerPrivilegesModulesDesc")->updateData($desc, $info);
            $return = true;
            foreach ($ret_info as $key => $value) {
                if (false === $value) {
                    return false;
                }
            }
            return $return;
        } else {
            return false;
        }
    }
    /**
     * [managerPrivilegesModuleInfo description]获取权限单条信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-19
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function managerPrivilegesModuleInfo($id = '')
    {
        if ($id > 0) {
            $data = array();
            $data['manager_privileges_modules.mpm_id'] = $id;
            $data['leftjoin'] = array('manager_privileges_modules_desc', "manager_privileges_modules.mpm_id = manager_privileges_modules_desc.mpm_id");
        } else {
            return false;
        }
        if ($data) {
            $info = model("ManagerPrivilegesModules")->listDatas($data, 1);
            if (empty($info)) {
                return false;
            } else {
                return $info;
            }
        } else {
            return false;
        }

    }
    /**
     * 权限模块删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDelete($id = '')
    {

        (isset($id) && $id > 0) ? $param['id'] = $id : false;
        $data['mpm_status'] = 0;
        $info = model("ManagerPrivilegesModules")->delData($data, $param);
        if ($info) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 管理员分组列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @param  string            $value [description]
     */
    public function managersPrivilegesGroupData($data = array(), $type = 0)
    {
        $info = array();
        /**
         * 分页
         */
        (isset($data['page']) && !empty($data['page'])) ? $info['page'] = $data['page'] : false;
        /**
         * where语句
         */
        (isset($data['mpg_name']) && !empty($data['mpg_name'])) ? $info['walk']['where']["like"] = array("mpg_name", "%" . $data['mpg_name'] . "%") : false;
        (isset($data['mpg_status']) && $data['mpg_status'] >= -1) ? $info['mpg_status'] = $data['mpg_status'] : false;
        (isset($data['mpg_author']) && !empty($data['mpg_author'])) ? ($info['mpg_author'] = $data['mpg_author']) : false;
        (isset($data['mpg_editor']) && !empty($data['mpg_editor'])) ? ($info['mpg_editor'] = $data['mpg_editor']) : false;

        $mpg_data = model("ManagersPrivilegesGroup")->listDatas($info, $type);

        $mpg_data = deepStripslashes($mpg_data);
        if (!empty($mpg_data)) {
            $m_data = model("Managers")->managersList();
            $m_list = array();
            if (!empty($m_data)) {
                foreach ($m_data as $key => $value) {
                    $m_list[$value['m_id']] = $value['m_name'];
                }
            }
            $list = $this->processTimeAndStatus($mpg_data,$m_list);
            if (!empty($m_data)) {
                $list['m_list'] = $m_data;
            }
            if (!empty($list)) {
                return $list;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * 添加分组
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managersPrivilegesGroupInsert($data = array())
    {
        /**
         * 如果传输过来的数据是空, 出错
         */
        if (empty($data)) {
            return false;
            $this->http->finish();
        }
        /**
         * 处理完的数据是空, 出错
         */
        $data = deepAddslashes($data);
        $data['mpg_in_time'] = time();
        $data['mpg_edit_time'] = time();

        if (!is_array($data)) {
            return false;
            $this->http->finish();
        }
        return model("ManagersPrivilegesGroup")->addData($data);
    }
    /**
     * 修改分组信息
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managersPrivilegesGroupUpdate()
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
    public function managersPrivilegesGroupDelete()
    {

    }
    /**
     * 检查用户名是否存在
     * @param  [type]  $name 管理员名称
     * @return  int    大于0存在
     */
    public function hasManagerName($name)
    {
        $where = array('m_name' => $name);
        $res = model("Managers")->select('m_id')->where($where)->fetch();
        return $res;
    }

    /**
     * 用登陆户登录检测用户名密码是否对应
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

        $res = model("Managers")->infoDatas($data);

        if ($res['m_id'] > 0 && $res['m_status'] >= 0) {
            model("Managers")->
                updateData(
                array(
                    'where' => 'm_id',
                    'm_id' => $res['m_id'],
                    'm_last_time' => time(),
                    'm_last_ip' => ip2long(\Swoole::$php->request->getClientIP()),
                )
            );
            /**
             * echo model("Managers")->db->getSql();die;
             */
            return $res;

        } else {

            return false;
        }
    }
    /**
     * [processTimeAndStatus description] 处理时间和状态
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-21
     * @param  array             $data
     * @return [array]                 $info
     */
    public function processTimeAndStatus($data = array(),$list = array())
    {
        $arr = array(
            "-1" => "已废弃",
            "0" => "不可用",
            "1" => "未开启",
            "2" => "正常",
        );
        if (isset($data['data']) && !empty($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $data['data'][$key]['mpg_status'] = $arr[$value['mpg_status']];
                $data['data'][$key]['mpg_in_time'] = date("Y-m-d H:i:s", $value['mpg_in_time']);
                $data['data'][$key]['mpg_edit_time'] = date("Y-m-d H:i:s", $value['mpg_edit_time']);
                if (!empty($list)) {
                    $data['data'][$key]['mpg_author_name'] = $list[$value['mpg_author']];
                    $data['data'][$key]['mpg_editor_name'] = $list[$value['mpg_editor']];
                }
            }
        }
        return $data;
    }
}
