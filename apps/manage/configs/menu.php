<?php
/**
 * 后台菜单列表(菜单根据实际模板设置)
 * @param  string  name 中文名称(每级菜单都有中文名称)
 * @param  string  icon 图标(只有二级菜单有图标)
 * @param  string  link 链接地址(二级菜单,三级菜单才会有, 二级菜单可有可没有)
 * @param  array   sub_menu 下级菜单数组
 * @return array $menu 菜单数组
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-08-15
 */
$menu = array(
	/**
	 * 系统设置
	 */
    0 => array(
        'name' => '系统设置', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '首页', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '/index/index',
            ),
            1 => array(
                "name" => 'SEO设置', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '关键字设置', 'icon' => '', 'link' => '/system/seoKeyword',
                    ),
                    1 => array(
                        'name' => '敏感词设置', 'icon' => '', 'link' => '/system/sensitiveWords',
                    ),
                ),
            ),
        ),
    ),
    /**
     * 管理设置
     */
    1 => array(
        'name' => '管理设置', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '管理员', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '管理员列表', 'icon' => '', 'link' => '/manager/managerList',
                    ),
                    1 => array(
                        'name' => '管理员添加', 'icon' => '', 'link' => '/manager/managerAdd',
                    ),
                ),
            ),
            1 => array(
                "name" => '权限分组', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '分组列表', 'icon' => '', 'link' => '/manager/managersPrivilegesGroupList',
                    ),
                    1 => array(
                        'name' => '分组添加', 'icon' => '', 'link' => '/manager/managersPrivilegesGroupAdd',
                    ),
                ),
            ),
            2 => array(
                "name" => '权限模块', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '模块列表', 'icon' => '', 'link' => '/manager/managerPrivilegesModulesList',
                    ),
                    1 => array(
                        'name' => '模块添加', 'icon' => '', 'link' => '/manager/managerPrivilegesModulesAdd',
                    ),
                ),
            ),
        ),
    ),
    /*文章管理*/
    2 => array(
        'name' => '文章管理', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '文章分类管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '文章分类列表', 'icon' => '', 'link' => '/Articles/categoryList',
                    ),
                    1 => array(
                        'name' => '文章分类添加', 'icon' => '', 'link' => '/Articles/categoryAdd',
                    ),
                ),
            ),
            1 => array(
                "name" => '文章管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '文章列表', 'icon' => '', 'link' => '/Articles/index',
                    ),
                    1 => array(
                        'name' => '文章添加', 'icon' => '', 'link' => '/Articles/articlesAdd',
                    ),
                ),
            ),
        ),
    ),
);

return $menu;
