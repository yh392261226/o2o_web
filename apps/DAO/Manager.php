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
    public function managerData()
    {
        # code...
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
    public function managerDelete($m_id)
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
    public function managerPrivilegesModulesData($data = array())
    {
        if(!empty($data)){
            $info = array();
            /**
             * 分页
             */
            (isset($data['page']) && !empty($data['page'])) ? $info['page'] = $data['page'] : false;
            /**
             * where语句
             */
            (isset($data['mpm_name']) && !empty($data['mpm_name'])) ? $info['where']['mpm_name'] = $data['mpm_name'] : false;
            (isset($data['mpm_status']) && !empty($data['mpm_status'])) ? $info['where']['mpm_status'] = $data['mpm_status'] : false;
            /**
             * leftjoin 数组 两值  第一个表, 第二个关联的内容
             */
            $info['leftjoin'] = array('manager_privileges_modules_desc',"manager_privileges_modules.mpm_id = manager_privileges_modules_desc.mpm_id");
            // $info['pagesize'] = 1;
            $mpm_data = model("ManagerPrivilegesModules")->listDatas($info);
            $mpm_data = deepStripslashes($mpm_data);
            if (!empty($mpm_data)) {
                return $mpm_data;
            }else{
                return false;
            }
        }else{
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
    public function managerPrivilegesModulesInsert($data)
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
    public function managerPrivilegesModulesUpdate()
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
    public function managerPrivilegesModulesDelete()
    {
        # code...
    }
    /**
     * 模块描述列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDescData()
    {
        # code...
    }
    /**
     * 模块描述表添加
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDescInsert()
    {
        # code...
    }
    /**
     * 模块描述修改
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDescUpdate()
    {
        # code...
    }
    /**
     * 模块描述删除
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDescDelete()
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
    public function managersPrivilegesGroupData()
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
    public function managersPrivilegesGroupInsert()
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
        # code...
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

        $res    = model("Managers")->infoDatas($data);

        if ($res['m_id'] > 0 && $res['m_status'] >= 0) {
            model("Managers")->
                saveData(
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
}
