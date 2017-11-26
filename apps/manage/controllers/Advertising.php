<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-04 17:53:06
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-11-09 16:29:12
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

        $this->tpl->assign("list",$list);
        $this->tpl->display("Advertising/index.html");
    }



    /*广告添加*/
    public function advertisingAdd()
    {
        /*获取地区数组*/
        $area = area(1);
        $this->tpl->assign("area_provinces",$area['regions']);


        if (defined('HTMLEDITOR')) {
            $this->tpl->assign("htmleditor", HTMLEDITOR);
        }
        $this->tpl->display("Advertising/advertisingAdd.html");
    }



    /*广告添加数据操作*/
    public function doAdvertisingAdd()
    {
        $jump = "/Advertising/AdvertisingAdd";
        $dao_advertising = new \MDAO\Advertising(array('table'=>'advertising'));
        if(!isset($_POST['a_title']) || empty($_POST['a_title']) ){
            msg("请填写广告标题", $status = 0, $jump);
        }

        $data = array();
        $data['a_title'] = trim($_POST['a_title']);
        $data['a_info'] = isset($_POST['a_info'])&&!empty($_POST['a_info'])?deepAddslashes(htmlspecialchars($_POST['a_info'])):"";
        $data['a_in_time'] = time();
        $data['a_author'] = parent::$manager_status;
        $data['a_last_edit_time'] = time();
        $data['a_last_editor'] = parent::$manager_status;
        $data['r_id'] = isset($_POST['r_id'])&&!empty($_POST['r_id'])?intval($_POST['r_id']):1;
        $data['a_status'] = isset($_POST['a_status'])?intval($_POST['a_status']):0;
        $data['a_type'] = isset($_POST['a_type'])?intval($_POST['a_type']):0;
        $data['a_link'] = isset($_POST['a_link'])?trim($_POST['a_link']):"";
        $data['a_start_time'] = isset($_POST['a_start_time']) && intval($_POST['a_start_time']) > 0?strtotime($_POST['a_start_time']):0;
        $data['a_end_time'] =  isset($_POST['a_end_time']) && intval($_POST['a_end_time']) > 0?strtotime($_POST['a_end_time']):0;
        $data['a_position'] = (isset($_POST['a_position']) && trim($_POST['a_position']) != '') ? trim($_POST['a_position']) : '';

        /*判断插入数据长度*/
        if(isset($data['a_title']) && mb_strlen($data['a_title'],'utf8') > 255){
            msg("广告标题的最大字符长度为255!", $status = 0, $jump);
        }
        if(isset($data['a_link']) && mb_strlen($data['a_link'],'utf8') > 255){
            msg("广告链接的最大字符长度为40!", $status = 0, $jump);
        }
        if(isset($data['s_desc']) && mb_strlen($data['a_info'],'utf8') > 255){
            msg("简介的最大字符长度为60!", $status = 0, $jump);
        }


        if(isset($_FILES['a_img']['name'][0])&&!empty($_FILES['a_img']['name'][0])){

            $up_pic = $this->uploadAll('a_img','ad_');

            if (empty($up_pic))
            {
                msg("文件上传失败!", $status = 0, $jump);
            }else{


                    $up_pic = implode(",",$up_pic);


                    $data['a_img'] = $up_pic;
            }
        }



        $a_id = $dao_advertising ->addData($data);
            if($a_id){
                msg("文章添加成功", $status = 1, $jump);
            }
            msg("文章添加失败!", $status = 0, $jump);


    }

    /*删除广告*/
    public function advertisingDel()
    {
        $jump = "/Advertising/index";
        if(isset($_GET['a_id']) && !empty($_GET['a_id'])){
            $a_id = intval($_GET['a_id']);
            $dao_advertising = new \MDAO\Advertising(array('table'=>'advertising'));
            $res = $dao_advertising -> delData($a_id);
            if($res){
                msg("文件删除成功!", $status = 1, $jump);
            }
        }
        msg("文件删除失败!", $status = 0, $jump);
    }



/*广告修改*/
    public function advertisingEdit()
    {
        $jump = "/Advertising/index";
        $a_id = isset($_GET['a_id']) ? intval($_GET['a_id']) : 0;

        if($a_id == 0){
            msg("参数错误!", $status = 0, $jump);
        }

        /*获取地区数组*/
        $area = area(1);

        /*获取当前id数据*/
        $dao_article = new \MDAO\Advertising(array('table'=>'advertising'));
        $self_data = $dao_article->infoData(array(
            'a_id' => $a_id,
            'pager'=>false,
            'fields'=>'a_id,a_title,a_info,a_link,a_type,a_status,a_start_time,a_end_time,r_id,a_img, a_position',
                ));


        $self_data = $self_data['data'][0];

        /*获取地区名称*/
        $r_name = "";
        if($self_data['r_id'])
        {
            $r_name = area('','','',$self_data['r_id']);
        }
        $self_data['r_name'] = !empty($r_name) ? $r_name : "地区未定义";
        $this->tpl->assign("self_data",$self_data);
        $this->tpl->assign("area_provinces",$area['regions']);
        $this->tpl->display("Advertising/advertisingEdit.html");
    }


     /*广告修改数据操作*/
    public function doAdvertisingEdit()
    {

        $jump = "/Advertising/index";
        $dao_advertising = new \MDAO\Advertising(array('table'=>'advertising'));
        if(!isset($_POST['a_id']) || empty($_POST['a_id']) || !isset($_POST['a_title']) || empty($_POST['a_title'])){
             msg("参数不足", $status = 0, $jump);
        }

        $data = array();
        $a_id = intval($_POST['a_id']);
        $data['a_title'] = trim($_POST['a_title']);
        $data['a_info'] = isset($_POST['a_info'])&&!empty($_POST['a_info'])?deepAddslashes(htmlspecialchars($_POST['a_info'])):"";
        $data['a_last_edit_time'] = time();
        $data['a_last_editor'] = parent::$manager_status;
        $data['r_id'] = isset($_POST['r_id'])&&!empty($_POST['r_id'])?intval($_POST['r_id']):1;
        $data['a_status'] = isset($_POST['a_status'])?intval($_POST['a_status']):0;
        $data['a_type'] = isset($_POST['a_type'])?intval($_POST['a_type']):0;
        $data['a_link'] = isset($_POST['a_link'])?trim($_POST['a_link']):"";
        $data['a_start_time'] = isset($_POST['a_start_time']) && intval($_POST['a_start_time']) > 0?strtotime($_POST['a_start_time']):0;
        $data['a_end_time'] =  isset($_POST['a_end_time']) && intval($_POST['a_end_time']) > 0?strtotime($_POST['a_end_time']):0;
        $data['a_position'] = (isset($_POST['a_position']) && trim($_POST['a_position']) != '') ? trim($_POST['a_position']) : '';

        /*判断插入数据长度*/
        if(isset($data['a_title']) && mb_strlen($data['a_title'],'utf8') > 255){
            msg("广告标题的最大字符长度为255!", $status = 0, $jump);
        }
        if(isset($data['a_link']) && mb_strlen($data['a_link'],'utf8') > 255){
            msg("广告链接的最大字符长度为40!", $status = 0, $jump);
        }
        if(isset($data['s_desc']) && mb_strlen($data['a_info'],'utf8') > 255){
            msg("简介的最大字符长度为60!", $status = 0, $jump);
        }

        if(isset($_FILES['a_img']['name'][0])&&!empty($_FILES['a_img']['name'][0])){

            $up_pic = $this->uploadAll('a_img','ad_');


            if (empty($up_pic))
            {
                msg("文件上传失败!", $status = 0, $jump);
            }else{

                $up_pic = implode(",",$up_pic);
                $data['a_img'] = $up_pic;

                /*删除无效图片*/
                if(isset($_POST['img_name']) && !empty($_POST['img_name']) ){
                    $a_img_name = trim($_POST['img_name']);
                    $img_name_arr = explode(',',$a_img_name);
                    foreach ($img_name_arr  as  $val) {
                        $file_name = '';
                        $file_name = UPLOADPATH.'/images'.$val;
                        if(is_file($file_name)){
                            unlink($file_name);
                        }
                    }
                }
            }
        }



        $res = $dao_advertising ->updateData($data,array('a_id'=>$a_id));


        if($res){
            msg("广告修改成功", $status = 1, $jump);
        }

            msg("广告修改失败!", $status = 0, $jump);


    }






}