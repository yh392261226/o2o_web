<?php
namespace App\Controller;

/**
 * @action   首页
 * @author   户连超
 * @addtime  2017.08.01
 * @e-mail    zrkjhlc@gmail.com
 */
class Index extends \CLASSES\AdminBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $menu = $this->config['menu'];
        // foreach ($menu as $key => $variable) {

        //     echo $variable['name']."<br/>";

        //     if (isset($variable['sub_menu']) && $variable['sub_menu'] != '') {

        //         foreach ($variable['sub_menu'] as $key => $value) {

        //             echo "&nbsp;&nbsp;&nbsp;&nbsp;".$value['name']."<br>";

        //             if (isset($value['sub_menu']) && $value['sub_menu'] != '') {

        //                 foreach ($value['sub_menu'] as $ke => $val) {

        //                     echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$val['name']."<br>";

        //                 }

        //             }

        //         }  

        //     }
            
        // }
        $this->tpl->assign('menu_list', $menu);
        $this->tpl->display("Index/index.html");
    }
    public function welcome()
    {
        /* $smarty->display('file:index.tpl');
         * $smarty->display('db:index.tpl');
         * $smarty->display('index.tpl'); // will use default resource type
         * {include file="file:index.tpl"}
         * {include file="db:index.tpl"}
         * {include file="index.tpl"} {* will use default resource type *}
         */
        $this->tpl->display("Index/welcome.html");
    }
}
