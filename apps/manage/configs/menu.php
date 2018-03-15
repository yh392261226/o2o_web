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
                "name" => '支付设置', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '支付设置', 'icon' => '', 'link' => '/Payments/list',
                    ),
                    //1 => array(
                    //    'name' => '敏感词设置', 'icon' => '', 'link' => '/system/sensitiveWords',
                    //),
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
                        'name' => '管理员列表', 'icon' => '', 'link' => '/Managers/list',
                    ),
                    1 => array(
                        'name' => '权限组列表', 'icon' => '', 'link' => '/Managers/listGroup',
                    ),
                    2 => array(
                        'name' => '模块列表', 'icon' => '', 'link' => '/Managers/listModules',
                    ),
                ),
            ),
            1 => array(
                "name" => '地区', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '地区列表', 'icon' => '', 'link' => '/Regions/list',
                    ),
                ),
            ),
            2 => array(
                "name" => '红包', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '类型列表', 'icon' => '', 'link' => '/Bouns/listType',
                    ),
                    1 => array(
                        'name' => '红包列表', 'icon' => '', 'link' => '/Bouns/list',
                    ),
                ),
            ),
        ),
    ),
    /*文章管理*/
    2 => array(
        'name' => '文章管理', 'icon' => '', 'link' => '/Articles/index', 'sub_menu' => array(
            0 => array(
                "name" => '文章分类管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '/Articles/index', 'sub_menu' => array(
                    0 => array(
                        'name' => '文章分类列表', 'icon' => '', 'link' => '/Articles/categoryList',
                    ),
                    1 => array(
                        'name' => '文章列表', 'icon' => '', 'link' => '/Articles/index',
                    ),
                ),
            ),
            1 => array(
                "name" => '广告管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '/Advertising/index', 'sub_menu' => array(
                    0 => array(
                        'name' => '广告列表', 'icon' => '', 'link' => '/Advertising/index',
                    ),
                ),
            ),
        ),
    ),

    /*消息管理*/
    3 => array(
        'name' => '消息管理', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '投诉管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '投诉类型列表', 'icon' => '', 'link' => '/Complaints/categoryList',
                    ),
                    1 => array(
                        'name' => '投诉列表', 'icon' => '', 'link' => '/Complaints/index',
                    ),
                ),
            ),
            1 => array(
                "name" => '站内信', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '站内信列表', 'icon' => '', 'link' => '/Msg/list',
                    ),
                ),
            ),
        ),
    ),
    /*技能管理*/
    4 => array(
        'name' => '用户管理', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '用户管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '用户列表', 'icon' => '', 'link' => '/Users/list',
                    ),
                ),
            ),
        ),
    ),
    /*技能管理*/
    5 => array(
        'name' => '技能管理', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '技能管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '技能列表', 'icon' => '', 'link' => '/Skills/index',
                    ),
                ),
            ),
        ),
    ),
    /*任务*/
    6 => array(
        'name' => '任务管理', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '任务管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '任务列表', 'icon' => '', 'link' => '/Tasks/list',

                    ),
                ),
            ),
        ),
    ),


    /*日志管理*/
    7 => array(
        'name' => '日志管理', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '日志管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '任务日志', 'icon' => '', 'link' => '/Log/orders',
                    ),
                    1 => array(
                        'name' => '平台资金流向记录', 'icon' => '', 'link' => '/Log/platformFunds',
                    ),
                    2 => array(
                        'name' => '用户充值记录', 'icon' => '', 'link' => '/Log/userRecharge',
                    ),
                    3 => array(
                        'name' => '用户提现记录', 'icon' => '', 'link' => '/Log/userWithdraw',
                    ),
                    4 => array(
                        'name' => '用户位置记录', 'icon' => '', 'link' => '/Log/userCurPosition',
                    ),
                )
            ),
        ),
    ),

    /*配置管理*/
    8 => array(
        'name' => '系统配置', 'icon' => '', 'link' => '', 'sub_menu' => array(
            0 => array(
                "name" => '系统配置', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => array(
                    0 => array(
                        'name' => '前台配置', 'icon' => '', 'link' => '/Application_config/index',
                    ),
                    1 => array(
                        'name' => '后台配置', 'icon' => '', 'link' => '/Web_config/index',
                    ),
                )
            ),
        ),
    ),


);

return $menu;
