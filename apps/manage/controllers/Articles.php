<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-16 14:10:54
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
        $a = "";
        var_dump(isset($a));die;
        /*获取地区数组*/
        $area = area(1);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/addCategory.html");
    }
    /*处理文章添加数据*/
    public function doAddCategory()
    {
        if(isset($_POST['ac_name']) || isset($_POST['ac_pid']) || empty($_POST['ac_pid']) || empty($_POST['ac_name'])){

        }
        $data = array();
        $data['ac_pid'] = intval($_POST['ac_pid']);
        $data['ac_name'] = trim($_POST['ac_name']);
        $data['ac_desc'] = isset($_POST['ac_desc'])&&!empty($_POST['ac_desc'])?deepAddslashes(htmlspecialchars($_POST['ac_desc'])):"";
        $data['ac_status'] = isset($_POST['ac_status']);

    }

    /*ajax请求地区*/
    public function ajaxArea()
    {
        $parent = !empty($_GET['parent']) ? intval($_GET['parent']) : "";
        $type = !empty($_GET['type']) ? intval($_GET['type']) : "";
        $target = !empty($_GET['target']) ? trim($_GET['target']) : '';
        if(empty($parent)||empty($type)||empty($target)){
            $this->http->finish($this->json("传入信息不完整",0));
        }
        // $data = table('regions')->select('*')->fetchall();
        // file_put_contents("../../area.php",serialize($data));
        $res = area($parent,$type,$target);
        if($res){
            $this->http->finish($this->json($res,1));
        }else{
            $this->http->finish($this->json("数据获取失败",0));
        }


    }
}
