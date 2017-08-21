<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-21 11:07:15
 */

namespace App\Controller;

class Articles extends \CLASSES\AdminBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function categoryList()
    {
        $dao_article = new \DAO\Articles();
        /*获取分类数组*/
        $arr_ac = $dao_article->getChildTree(0);
        $this->tpl->assign('ac_data',json_encode($arr_ac));
        $this->tpl->display("Articles/categoryList.html");
    }
    /*加载文章分类添加模板*/
    public function categoryAdd()
    {
        /*获取地区数组*/
        $area = area(1);

        /*获取分类树*/
        $dao_article = new \DAO\Articles();
        $ac_tree = $dao_article->getTree();

        $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/categoryAdd.html");
    }
    /*处理文章添加数据*/
    public function docategoryAdd()
    {
        $jump = "/Articles/categoryAdd";
        $dao_article = new \DAO\Articles();
        if(!isset($_POST['ac_name']) || !isset($_POST['ac_pid']) || empty($_POST['ac_pid']) || empty($_POST['ac_name'])){
            msg("请填写分类名并选择父分类名", $status = 0, $jump);
        }else{
            /*判断分类名是否存在*/

            $res = $dao_article->getIdByName($_POST['ac_name']);

            if(intval($res) > 0){
                msg("分类名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['ac_pid'] = intval($_POST['ac_pid']);
        $data['ac_name'] = trim($_POST['ac_name']);
        $data['ac_desc'] = isset($_POST['ac_desc'])&&!empty($_POST['ac_desc'])?deepAddslashes(htmlspecialchars($_POST['ac_desc'])):"";
        $data['ac_status'] = isset($_POST['ac_status'])?intval($_POST['ac_status']):0;
        if(isset($_FILES['ac_img']['name'])&&!empty($_FILES['ac_img']['name'])){

            /*获取文件后缀名*/
            // $mime = $_FILES['ac_img']['type'];
            // $filetype = $this->getMimeType($mime);
            /*文件名*/
            $file_name = 'ac_'.time().rand(1000,9999);
            /*子目录*/
            // $this->upload->sub_dir = 'images';
            /*子目录生成参数*/
            $this->upload->shard_argv = 'Y/m/d';
            /*子目录生成方法，可以使用randomkey，或者date,user*/
            $this->upload->shard_type = 'date';
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


    /**
     * 获取MIME对应的扩展名
     * @param $mime
     * @return bool
     */
    public function getMimeType($mime)
    {
        $mimes = require LIBPATH . '/data/mimes.php';
        if (isset($mimes[$mime]))
        {
            return $mimes[$mime];
        }
        else
        {
            return false;
        }
    }

    /**
     * 删除文章分类
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-08-18
     * @return bool           [description]
     */
    public function categoryDel()
    {
        $jump = "/Articles/categoryList";
        $ac_id = isset($_GET['ac_id']) ? intval($_GET['ac_id']) : 0;
        if($ac_id == 0)
        {
            msg("参数错误,删除失败!", $status = 0, $jump);
        }else{
            /*判断有没有子集和该分类内有没有文件如果有删除失败*/
            $dao_article = new \DAO\Articles();
            $child_cat_id = $dao_article->catChild($ac_id);
            if(!$child_cat_id)
            {

                $child_art_id = $dao_article->artChild($ac_id);
                if(!$child_art_id)
                {

                    $res = $dao_article->delCategory($ac_id);
                    if($res)
                    {
                        msg("分类删除成功!", $status = 1, $jump);
                    }else{
                        msg("分类删除失败!", $status = 0, $jump);
                    }
                }else{
                    msg("该分类下有文章,请先将文章修改或删除!", $status = 0, $jump);
                }
            }else{
                msg("该分类下有子分类,请先将子分类修改或删除!", $status = 0, $jump);
            }


        }


    }

    /*文章分类修改*/
    public function categoryEdit()
    {
        $jump = "/Articles/categoryList";
        $ac_id = isset($_GET['ac_id']) ? intval($_GET['ac_id']) : 0;

        if($ac_id == 0){
            msg("参数错误!", $status = 0, $jump);
        }

        /*获取地区数组*/
        $area = area(1);

        /*获取除了自己子集的分类树*/
        $dao_article = new \DAO\Articles();
        $ac_tree = $dao_article->getTreeExceptChild($ac_id);

        $self_data = $dao_article->getCategory($ac_id);
        $self_data = $self_data[0];
        $r_name = "";
        if($self_data['r_id'])
        {
            $r_name = area('','','',$self_data['r_id']);
        }
        $self_data['r_name'] = !empty($r_name) ? $r_name : "地区未定义";


        $this->tpl->assign("self_data",$self_data);
        $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/categoryEdit.html");
    }


     /*处理文章添加数据*/
    public function doCategoryEdit()
    {

        $jump = "/Articles/categoryList";
        $ac_id = isset($_POST['ac_id']) ? intval($_POST['ac_id']) : 0;
        if($ac_id == 0){
            msg("参数错误修改失败!", $status = 0, $jump);
        }

        $dao_article = new \DAO\Articles();
        if(!isset($_POST['ac_name']) || !isset($_POST['ac_pid']) || empty($_POST['ac_pid']) || empty($_POST['ac_name'])){
            msg("请填写分类名并选择父分类名", $status = 0, $jump);
        }else{
            /*判断分类名是否存在*/

            $res = $dao_article->getIdByName($_POST['ac_name']);

            if(intval($res['ac_id']) > 0 && $res['ac_id'] != $ac_id){
                msg("分类名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['ac_pid'] = intval($_POST['ac_pid']);
        $data['ac_name'] = trim($_POST['ac_name']);
        $data['ac_desc'] = isset($_POST['ac_desc'])&&!empty($_POST['ac_desc'])?deepAddslashes(htmlspecialchars($_POST['ac_desc'])):"";
        $data['ac_status'] = isset($_POST['ac_status'])?intval($_POST['ac_status']):0;
        if(isset($_FILES['ac_img']['name'])&&!empty($_FILES['ac_img']['name'])){

            /*获取文件后缀名*/
            // $mime = $_FILES['ac_img']['type'];
            // $filetype = $this->getMimeType($mime);
            /*文件名*/
            $file_name = 'ac_'.time().rand(1000,9999);
            /*子目录*/
            // $this->upload->sub_dir = 'images';
            /*子目录生成参数*/
            $this->upload->shard_argv = 'Y/m/d';
            /*子目录生成方法，可以使用randomkey，或者date,user*/
            $this->upload->shard_type = 'date';
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

                //删除原图标

            }
        }
        $data['ac_last_edit_time'] = time();
        $data['ac_last_editor'] = $_SESSION['m_id'];
        $data['r_id'] = isset($_POST['r_id'])&&!empty($_POST['r_id'])?intval($_POST['r_id']):1;

        $where = array();
        $where['id'] = $ac_id;
        $where['where'] = 'ac_id';
        $res = $dao_article->updateArticeCat($data,$where);
        if($res){
            msg("分类修改成功", $status = 1, $jump);
        }else{
            msg("分类修改失败!", $status = 0, $jump);
        }

    }







/**********************************************************文章部分******************************************************************/




    /**
     * 通过分类id查看文章id
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-08-18
     * @param  [type]            $ac_id [description]
     * @return array                  [description]
     */
    public function getArticlesByCateId($ac_id)
    {
        $dao_article = new \DAO\Articles();
        return $dao_article->getArticlesByCateId($ac_id);
    }



    /*文章列表默认为首页*/
    public function index()
    {

    }

    /*文章添加*/
    public function articleAdd()
    {
        /*获取地区数组*/
        $area = area(1);

        /*获取分类树*/
        $dao_article = new \DAO\Articles();
        $ac_tree = $dao_article->getTree();

        $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/articleAdd.html");
    }

    /*文章添加数据操作*/
    public function doArticleAdd()
    {
        var_dump($_POST);
    }

}
