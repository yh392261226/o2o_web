<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-19 14:11:23
 */

namespace App\Controller;

class Articles extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function categoryList()
    {
        $dao_article = new \WDAO\Articles(array('table'=>'Articles_category'));
        /*获取分类数组*/
        $arr_ac = $dao_article->getChildTree();
        $this->exportData( $arr_ac,1);
    }


/**********************************************************文章部分******************************************************************/



    /*文章列表默认为首页*/
    public function articlesList()
    {
        $condition = array();
        $condition['ac_id'] = isset($_GET['ac_id'])&&!empty($_GET['ac_id']) ? intval($_GET['ac_id']) : 0;

        /*获取文章列表*/
        $dao_article = new \WDAO\Articles(array('table'=>'Articles'));
        $artivle_list_arr = $dao_article->getArticleList($condition);
        var_dump($artivle_list_arr);

    }



















}
