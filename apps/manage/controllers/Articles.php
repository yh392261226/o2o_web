<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-16 17:31:28
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
        $jump = "/Articles/addCategory";
        if(isset($_POST['ac_name']) || isset($_POST['ac_pid']) || empty($_POST['ac_pid']) || empty($_POST['ac_name'])){

        }
        $data = array();
        $data['ac_pid'] = intval($_POST['ac_pid']);
        $data['ac_name'] = trim($_POST['ac_name']);
        $data['ac_desc'] = isset($_POST['ac_desc'])&&!empty($_POST['ac_desc'])?deepAddslashes(htmlspecialchars($_POST['ac_desc'])):"";
        $data['ac_status'] = isset($_POST['ac_status'])?intval($_POST['ac_status']):0;
        if(isset($_FILES['ac_img']['name'])&&!empty($_FILES['ac_img']['name'])){
            $this->upload->sub_dir = '/images/article';
            $this->upload->shard_type = 'user';
            $file_name = 'ac_'.md5(time()).rand(1000,9999);
             //自动压缩图片
            $this->upload->max_width = 60; /*约定图片的最大宽度*/
            $this->upload->max_height = 60; /*约定图片的最大高度*/
            $this->upload->max_qulitity = 90; /*图片压缩的质量*/
            /*第一个参数是文件name名;第二个参数是自定义的文件名;第三个参数不知道干啥的*/
            $up_pic = $this->upload->save('ac_img',$file_name);
            if (empty($up_pic))
            {
                msg("文件上传失败!", $status = 0, $jump);
            }else{
                $data['ac_img'] = $up_pic['url'];
            }
        }
        $data['ac_in_time'] = time();
        $data['ac_author'] = $_SESSION['m_id'];
        $data['r_id'] = isset($_POST['r_id'])&&!empty($_POST['r_id'])?intval($_POST['r_id']):1;

        $dao_article = new \DAO\Articles();
        $res = $dao_article->saveArticeCat($data);
        if($res){
            msg("分类添加成功", $status = 1, $jump);
        }else{
            msg("分类添加失败!", $status = 0, $jump);
        }

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
