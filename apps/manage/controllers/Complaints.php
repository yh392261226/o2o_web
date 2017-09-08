<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-06 11:30:52
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-08 11:20:47
 */

namespace App\Controller;

class Complaints extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function categoryList()
    {
        $dao_complaint = new \MDAO\Complaints(array('table'=>'complaints_type'));
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
        /*获取分类数组*/
        $arr_ct = $dao_complaint ->listData($condition);

        $this->myPager($arr_ct['pager']);

        $this->tpl->assign('ct_data',$arr_ct['data']);
        $this->tpl->display("Complaints/categoryList.html");
    }
    /*加载投诉分类添加模板*/
    public function categoryAdd()
    {
        $this->tpl->display("Complaints/categoryAdd.html");
    }
    /*处理投诉添加数据*/
    public function docategoryAdd()
    {
        $jump = "/Complaints/categoryAdd";
        $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints_type'));
        if(!isset($_POST['ct_name']) || !isset($_POST['ct_name'])){
            msg("请填写类型名称", $status = 0, $jump);
        }else{
            /*判断分类名是否存在*/
            $res = $dao_complaints ->infoData(array('key'=>'ct_name','val'=>$_POST['ct_name'],'fields'=>'ct_id'));


            if(intval($res) > 0){
                msg("类型名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['ct_name'] = trim($_POST['ct_name']);
        $data['ct_status'] = isset($_POST['ct_status'])?intval($_POST['ct_status']):0;

        $res = $dao_complaints ->addData($data);
        if($res){
            msg("投诉类型添加成功", $status = 1, $jump);
        }else{
            msg("投诉类型添加失败!", $status = 0, $jump);
        }

    }




    /*文章分类修改*/
    public function categoryEdit()
    {
        $jump = "/Complaints/categoryList";
        $ct_id = isset($_GET['ct_id']) ? intval($_GET['ct_id']) : 0;

        if($ct_id == 0){
            msg("参数错误!", $status = 0, $jump);
        }



        $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints_type'));


        $self_data = $dao_complaints ->infoData(array('key'=>'ct_id','val'=>$ct_id));



        $this->tpl->assign("self_data",$self_data);
        $this->tpl->display("Complaints/categoryEdit.html");
    }


     /*处理文章分类修改数据*/
    public function doCategoryEdit()
    {

       $jump = "/Complaints/categoryList";
        $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints_type'));

        if(!isset($_POST['ct_name']) || empty($_POST['ct_name']) || !isset($_POST['ct_id']) || empty($_POST['ct_id'])){
            msg("参数不足", $status = 0, $jump);
        }else{
            /*判断分类名是否存在*/
            $res = $dao_complaints ->infoData(array('key'=>'ct_name','val'=>$_POST['ct_name'],'fields'=>'ct_id'));
            $ct_id = intval($_POST['ct_id']);


            if(intval($res['ct_id']) > 0 && $res['ct_id'] != $ct_id){
                msg("类型名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['ct_name'] = trim($_POST['ct_name']);
        $data['ct_status'] = isset($_POST['ct_status'])?intval($_POST['ct_status']):0;

        $res = $dao_complaints -> updateData($data,array('ct_id' => $ct_id));
        if($res){
            msg("投诉类型修改成功", $status = 1, $jump);
        }else{
            msg("投诉类型修改失败!", $status = 0, $jump);
        }

    }







/**********************************************************文章部分******************************************************************/



    /*投诉列表默认为首页*/
    public function index()
    {
        $condition = array();
        $condition['ct_id'] = isset($_GET['ct_id'])&&!empty($_GET['ct_id']) ? intval($_GET['ct_id']) : 0;
        $condition['c_status'] = isset($_GET['c_status']) ? intval($_GET['c_status']) : -100;/*0状态为未处理*/
        $condition['search_condition'] = isset($_GET['search_condition'])&&!empty($_GET['search_condition']) ? $_GET['search_condition'] : "";
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;

        /*获取投诉列表*/
        $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints'));
        $complaints_list_arr = $dao_complaints->getComplaintsList($condition);

        /*获取投诉分类*/
        $dao_complaint = new \MDAO\Complaints(array('table'=>'complaints_type'));
        $arr_ct = $dao_complaint ->listData(array('pager'=>false,'fields'=>'ct_id,ct_name'));

        /*获取管理员列表*/
        $dao_managers = new \MDAO\Managers();
        $manager_list = $dao_managers -> listData(array('pager'=>false,'fields'=>'m_id,m_name'));
        $manager_list = $manager_list['data'];



        foreach ($complaints_list_arr['data'] as $key => &$value) {
            foreach ($manager_list as $k => $v) {

                if(isset($value['c_last_editor'])&&isset($v['m_id'])){

                    if($value['c_last_editor'] == $v['m_id']){

                    $value['c_last_editor']  = $v['m_name'];

                    break;
                    }
                }

            }
        }


        $this->myPager($complaints_list_arr['pager']);

        if(!empty($condition['search_condition'])){
            $this->tpl->assign("search_condition",$condition['search_condition']);
        }
        if($condition['ct_id'] > 0){
            $this->tpl->assign("ct_id",$condition['ct_id']);
        }

            $this->tpl->assign("c_status",$condition['c_status']);


        $this->tpl->assign('ct_data',$arr_ct['data']);
        $this->tpl->assign("complaints_list_arr",$complaints_list_arr);
        $this->tpl->display("Complaints/index.html");
    }

    public function changeStatus()
    {
       if(isset($_GET['c_id'],$_GET['c_status'])&&!empty($_GET['c_id'])){
            $c_id = intval($_GET['c_id']);
            $c_status = intval($_GET['c_status']);

            $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints'));
            $res = $dao_complaints ->updateData(array('c_status'=>$c_status),array('c_id'=>$c_id));

            var_dump($res);
            if($res){
                echo 1;
            }

       }else{
         echo 0;
       }
    }



















}
