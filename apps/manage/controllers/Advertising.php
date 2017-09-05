<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-04 17:53:06
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-04 18:32:17
 */
namespace App\Controller;


class Advertising extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }

/*广告列表*/
    public function index()
    {
        $condition = array();
        $condition['search_condition'] = isset($_GET['search_condition'])&&!empty($_GET['search_condition']) ? $_GET['search_condition'] : "";
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;

        /*获取文章列表*/
        $dao_advertising = new \MDAO\Advertising(array('table'=>'Advertising'));
        $list = $dao_advertising -> advertisingList($condition);



        $this->myPager($list['pager']);


        if(!empty($condition['search_condition'])){
            $this->tpl->assign("search_condition",$condition['search_condition']);
        }

        // $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("list",$list);
        $this->tpl->display("Advertising/index.html");
    }


}