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
        # code...
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
        # code...
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
     * 模块描述列表
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
    public function managerPrivilegesModulesDescList()
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
    public function managerPrivilegesModulesDescAdd()
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
    public function managerPrivilegesModulesDescEdit()
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
    public function managerPrivilegesModulesDescDel()
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
