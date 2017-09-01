<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 15:57:38
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-01 19:33:32
 */

namespace App\Controller;

class Articles extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function categoryList()
    {
        $dao_article = new \MDAO\Articles(array('table'=>'Articles_category'));
        /*获取分类数组*/
        $arr_ac = $dao_article->getChildTree();
        $this->tpl->assign('ac_data',json_encode($arr_ac));
        $this->tpl->display("Articles/categoryList.html");
    }
    /*加载文章分类添加模板*/
    public function categoryAdd()
    {
        // $area = model('Articles')->getArea();
        /*获取地区数组*/
        $area = area(1);

        /*获取分类树*/
        $dao_article = new \MDAO\Articles(array('table'=>'Articles_category'));
        $ac_tree = $dao_article->getTree();

        $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/categoryAdd.html");
    }
    /*处理文章添加数据*/
    public function docategoryAdd()
    {
        $jump = "/Articles/categoryAdd";
        $dao_article = new \MDAO\Articles(array('table'=>'Articles_category'));
        if(!isset($_POST['ac_name']) || !isset($_POST['ac_pid']) || empty($_POST['ac_pid']) || empty($_POST['ac_name'])){
            msg("请填写分类名并选择父分类名", $status = 0, $jump);
        }else{
            /*判断分类名是否存在*/
            $res = $dao_article->infoData(array('key'=>'ac_name','val'=>$_POST['ac_name'],'fields'=>'ac_id'));


            if(intval($res) > 0){
                msg("分类名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['ac_pid'] = intval($_POST['ac_pid']);
        $data['ac_name'] = trim($_POST['ac_name']);
        $data['ac_info'] = isset($_POST['ac_info'])&&!empty($_POST['ac_info'])?deepAddslashes(htmlspecialchars($_POST['ac_info'])):"";
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
        $data['ac_last_edit_time'] = time();
        $data['ac_last_editor'] = $_SESSION['m_id'];
        $data['r_id'] = isset($_POST['r_id'])&&!empty($_POST['r_id'])?intval($_POST['r_id']):1;
        $res = $dao_article->addData($data);
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
            $dao_articles_category = new \MDAO\Articles(array('table'=>'Articles_category'));
            $child_cat_id = $dao_articles_category ->infoData(array('key'=>'ac_pid','val'=>$ac_id,'fields'=>'ac_id'));
            if(!$child_cat_id['ac_id'])
            {

                $dao_article = new \MDAO\Articles(array('table'=>'Articles'));
                $child_art_id = $dao_article->infoData(array('key'=>'ac_id','val'=>$ac_id,'fields'=>'a_id'));
                if(!$child_art_id['a_id'])
                {

                    $res = $dao_articles_category->delData($ac_id);
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
        $dao_article = new \MDAO\Articles(array('table'=>'Articles_category'));
        $ac_tree = $dao_article->getTreeExceptChild($ac_id);

        $self_data = $dao_article->infoData(array('key'=>'ac_id','val'=>$ac_id));

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

        $dao_article = new \MDAO\Articles(array('table'=>'Articles_category'));
        if(!isset($_POST['ac_name']) || !isset($_POST['ac_pid']) || (empty($_POST['ac_pid'])&&$_POST['ac_pid']!=="0") || empty($_POST['ac_name'])){
            msg("请填写分类名并选择父分类名", $status = 0, $jump);
        }else{
            /*判断分类名是否存在*/

            $res = $dao_article->infoData(array('key'=>'ac_id','val'=>$ac_id,'fields'=>'ac_id'));

            if(intval($res['ac_id']) > 0 && $res['ac_id'] != $ac_id){
                msg("分类名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['ac_pid'] = intval($_POST['ac_pid']);
        $data['ac_name'] = trim($_POST['ac_name']);
        $data['ac_info'] = isset($_POST['ac_info'])&&!empty($_POST['ac_info'])?deepAddslashes(htmlspecialchars($_POST['ac_info'])):"";
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

        $where = array('ac_id'=>$ac_id);
        $res = $dao_article->updateData($data,$where);
        if($res){
            msg("分类修改成功", $status = 1, $jump);
        }else{
            msg("分类修改失败!", $status = 0, $jump);
        }

    }







/**********************************************************文章部分******************************************************************/



    /*文章列表默认为首页*/
    public function index()
    {
        $condition = array();
        $condition['ac_id'] = isset($_GET['ac_id'])&&!empty($_GET['ac_id']) ? intval($_GET['ac_id']) : 0;
        $condition['search_condition'] = isset($_POST['search_condition'])&&!empty($_POST['search_condition']) ? $_POST['search_condition'] : "";
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;

        /*获取文章列表*/
        $dao_article = new \MDAO\Articles(array('table'=>'Articles'));
        $artivle_list_arr = $dao_article->getArticleList($condition);


        /*通过id获取用户名*/
        $dao_manager =  new \MDAO\Managers(array('table'=>'Managers'));
        foreach ($artivle_list_arr['data'] as $key => $value) {

            $artivle_list_arr['data'][$key]['a_last_edit_time'] = date('Y-m-d H:i:s',$artivle_list_arr['data'][$key]['a_last_edit_time']);
        }




        // var_dump($artivle_list_arr['pager']);die;

        /*获取分类树*/
        $dao_article = new \MDAO\Articles(array('table'=>'Articles_category'));
        $ac_tree = $dao_article->getTree();
        if(!empty($condition['search_condition'])){
            $this->tpl->assign("search_condition",$condition['search_condition']);
        }
        $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("page", $artivle_list_arr['page']);
        $this->tpl->assign("artivle_list_arr",$artivle_list_arr);
        $this->tpl->display("Articles/index.html");
    }

    /*文章修改*/
    public function articleEdit()
    {
        $jump = "/Articles/index";
        $ac_id = isset($_GET['a_id']) ? intval($_GET['a_id']) : 0;

        if($a_id == 0){
            msg("参数错误!", $status = 0, $jump);
        }

        /*获取地区数组*/
        $area = area(1);

        /*获取分类树*/
        $dao_article = new \MDAO\Articles();
        $ac_tree = $dao_article->getTree();


        /*获取当前id数据*/
        $self_data = $dao_article->getArticle($a_id);
        $self_data = $self_data;


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


    /*文章添加*/
    public function articleAdd()
    {
        /*获取地区数组*/
        $area = area(1);

        /*获取分类树*/
        $dao_article = new \MDAO\Articles();
        $ac_tree = $dao_article->getTree();

        $this->tpl->assign("ac_tree",$ac_tree);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Articles/articleAdd.html");
    }



    /*文章添加数据操作*/
    public function doArticleAdd()
    {
        $jump = "/Articles/articleAdd";
        $dao_article = new \MDAO\Articles();
        if(!isset($_POST['a_title']) || !isset($_POST['ac_id']) || empty($_POST['ac_id']) || empty($_POST['a_title'])){
            msg("请填写分类名并选择父分类名", $status = 0, $jump);
        }

        $data = array();
        $data['ac_id'] = intval($_POST['ac_id']);
        $data['a_title'] = trim($_POST['a_title']);
        $data['a_desc'] = isset($_POST['a_desc'])&&!empty($_POST['a_desc'])?htmlspecialchars($_POST['a_desc']):"";
        $data['a_info'] = isset($_POST['a_info'])&&!empty($_POST['a_info'])?deepAddslashes(htmlspecialchars($_POST['a_info'])):"";
        $data['a_in_time'] = time();
        $data['a_author'] = $_SESSION['m_id'];
        $data['r_id'] = isset($_POST['r_id'])&&!empty($_POST['r_id'])?intval($_POST['r_id']):1;
        $data['a_status'] = isset($_POST['ac_status'])?intval($_POST['a_status']):0;
        $data['a_top'] = isset($_POST['a_top'])?intval($_POST['a_top']):0;
        $data['a_recommend'] = isset($_POST['a_recommend'])?intval($_POST['a_recommend']):0;
        $data['a_link'] = isset($_POST['a_link'])?trim($_POST['a_status']):"";
        $data['a_start_time'] = isset($_POST['a_start_time'])?time($_POST['a_start_time']):0;
        $data['a_end_time'] = isset($_POST['a_end_time'])?time($_POST['a_end_time']):0;


        if(isset($_FILES['a_img']['name'][0])&&!empty($_FILES['a_img']['name'][0])){

            $up_pic = $this->uploadAll('a_img','a_');
            // var_dump($up_pic);die;

            if (empty($up_pic))
            {
                msg("文件上传失败!", $status = 0, $jump);
            }else{
                if(sizeof($up_pic)>1)
                {
                    $up_pic = implode(",",$up_pic);
                }else{
                    $data['a_img'] = $up_pic[0];
                }
            }
        }



        $res = $dao_article->saveArtice($data);
        if($res){
            msg("文章添加成功", $status = 1, $jump);
        }else{
            msg("文章添加失败!", $status = 0, $jump);
        }

    }

    /*多文件上传函数*/
    /*
    $form_name:表单中的name名;
    $prefix:生成的文件名的前缀;
    $size:约定图片的最大尺寸;
     */
    public function uploadAll($form_name,$prefix,$size=array('max_width'=>60,'max_height'=>60,'max_qulitity'=>90))
    {
        /*子目录生成参数*/
        $this->upload->shard_argv = 'Y/m/d';
        /*子目录生成方法，可以使用randomkey，或者date,user*/
        $this->upload->shard_type = 'date';
         //自动压缩图片
        $this->upload->max_width = $size['max_width']; /*约定图片的最大宽度*/
        $this->upload->max_height = $size['max_height']; /*约定图片的最大高度*/
        $this->upload->max_qulitity = $size['max_qulitity']; /*图片压缩的质量*/
        $data = $_FILES;
        $_FILES = array();
        $up_pic = array();
        if(!empty($data[$form_name]['name']))
        {
            foreach($data[$form_name]['name'] as $k=>$f)
            {
                $file_name = $prefix.time().rand(1000,9999);
                if(!empty($data[$form_name]['name'][$k]))
                {
                    $_FILES[$form_name]['name'] = $data[$form_name]['name'][$k];
                    $_FILES[$form_name]['type'] = $data[$form_name]['type'][$k];
                    $_FILES[$form_name]['tmp_name'] = $data[$form_name]['tmp_name'][$k];
                    $_FILES[$form_name]['error'] = $data[$form_name]['error'][$k];
                    $_FILES[$form_name]['size'] = $data[$form_name]['size'][$k];
                    $arr = $this->upload->save($form_name,$file_name);
                    $up_pic[] = $arr['url'];
                }
            }
        }
        return $up_pic;
    }

    /*获取数据库所有字段*/
    public function allFiels()
    {
        $result = $this->db->query("select COLUMN_NAME from information_schema.`COLUMNS` where TABLE_SCHEMA='o2o_hire' ")->fetchall();
        print_r($result);exit;
        $tables = array();
        while ($row=$result->fetch_row()){
            array_push($tables, $row[0]);
        }
        $fields_arr = array();
        foreach ($tables as $value) {
            $query = "desc {$value}";
            $result = $this->db->query($query);
            while ($row = $result->fetch_row()){
                array_push($fields_arr, $row[0]);
            }
        }

        file_put_contents('../public/fields_arr.php',var_export($fields_arr,true));

    }















}
