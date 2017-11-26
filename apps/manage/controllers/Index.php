<?php
namespace App\Controller;

/**
 * @action   首页
 * @author   户连超
 * @addtime  2017.08.01
 * @e-mail    zrkjhlc@gmail.com
 */
class Index extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }

    public function index()
    {
        //$menu = $this->config['menu'];
        //$this->tpl->assign('menu_list', $menu);
        $this->mydisplay("Index/index");
    }
    //
    public function welcome()
    {
        /* $smarty->display('file:index.tpl');
         * $smarty->display('db:index.tpl');
         * $smarty->display('index.tpl'); // will use default resource type
         * {include file="file:index.tpl"}
         * {include file="db:index.tpl"}
         * {include file="index.tpl"} {* will use default resource type *}
         */
        $this->mydisplay("Index/welcome");
    }
}
