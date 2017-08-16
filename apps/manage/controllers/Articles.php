<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-16 09:57:29
 */

namespace App\Controller;

class Articles extends \CLASSES\AdminBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $this->tpl->display("Articles/index.html");
    }
    /*加载文章分类添加模板*/
    public function addCategory()
    {
        /*获取地区数组*/
        $area = area(1);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/addCategory.html");
    }
    /*处理文章添加数据*/
    public function doAddCategory()
    {
        $data = array();

    }

    /*ajax请求地区*/
    public function ajaxArea()
    {
        $parent = !empty($_GET['parent']) ? intval($_REQUEST['parent']) : 0;
        $type = !empty($_GET['type']) ? intval($_REQUEST['type']) : 0;
        $target = !empty($_GET['target']) ? trim($_REQUEST['target']) : '';
        // $data = table('regions')->select('*')->fetchall();
        // file_put_contents("../../area.php",serialize($data));
        $res = area($parent,$type,$target);
        if($res){
            $this->http->finish($this->json($res,1));
        }else{
            $this->http->finish($this->json("",0));
        }


    }
}
