<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-11-14 15:48:24
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



    /*文章列表*/
    public function articlesList()
    {
        $condition = array();
        $condition['ac_id'] = isset($_GET['ac_id'])&&!empty($_GET['ac_id']) ? intval($_GET['ac_id']) : 0;
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 0;
        $condition['son'] = isset($_GET['son'])&&!empty($_GET['son']) ? intval($_GET['son']) : 0;
        $filter = isset($_GET['filter'])&&!empty($_GET['filter']) ? intval($_GET['filter']) : 1;/*默认过滤html标签*/

        /*获取文章列表*/
        $dao_article = new \WDAO\Articles(array('table'=>'Articles'));
        $article_list_arr = $dao_article->getArticleList($condition);

        if(count($article_list_arr['data']) == 1 && $article_list_arr['data'][0]['a_id'] > 0)
        {
            $dao_article = new \WDAO\Articles(array('table'=>'articles_ext'));
            $a_info = $dao_article ->infoData(array('key'=>'a_id','val'=>$article_list_arr['data'][0]['a_id']));
            $article_list_arr['data'][0]['a_desc'] = isset($a_info['a_desc']) ? htmlspecialchars_decode(htmlspecialchars_decode($a_info['a_desc'])) : '';
            if($article_list_arr['data'][0]['a_desc']){
                $article_list_arr['data'][0]['a_desc'] = strip_tags($article_list_arr['data'][0]['a_desc']);
            }
        }
        unset($article_list_arr['pager']);
        $this->exportData( $article_list_arr['data'],1);
    }


/**********************************************文章详情********************************************/

    public function articlesInfo()
    {
        if(empty($_GET['a_id'])){
            $this->exportData( array('msg'=>'请输入文章id'),0);
        }
        $filter = isset($_GET['filter'])&&!empty($_GET['filter']) ? intval($_GET['filter']) : 1;/*默认过滤html标签*/

        $a_id = isset($_GET['a_id'])&&!empty($_GET['a_id']) ? intval($_GET['a_id']) : 0;
        $dao_article = new \WDAO\Articles(array('table'=>'articles_ext'));
        $a_info = $dao_article ->infoData(array('key'=>'a_id','val'=>$a_id));

        $res = isset($a_info['a_desc']) ? htmlspecialchars_decode(htmlspecialchars_decode($a_info['a_desc'])) : '';


        if($filter){
            $res = strip_tags($res);

        }
        echo $res;die;
        $this->exportData(array('a_desc'=>$res),1);
    }


















}
