<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-06 11:30:52
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-09 13:33:25
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
        if (isset($_REQUEST['ct_type']) && $_REQUEST['ct_type'] > -1) $condition['ct_type'] = intval($_REQUEST['ct_type']);
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




    /*投诉分类修改*/
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


     /*处理投诉分类修改数据*/
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


            if(isset($res['ct_id']) && intval($res['ct_id']) > 0 && $res['ct_id'] != $ct_id){
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







/**********************************************************投诉部分******************************************************************/



    /*投诉列表默认为首页*/
    public function index()
    {
        //$this->db->debug = 1;
        $condition = $uids = $complaints_list_arr = array();
        if (isset($_GET['c_status']) && intval($_GET['c_status']) > -1) $condition['c_status'] = intval($_GET['c_status']);
        if (isset($_GET['ct_id']) && intval($_GET['ct_id']) > 0) $condition['ct_id'] = intval($_GET['ct_id']);
        if (isset($_GET['c_title']) && trim($_GET['c_title']) != '') $condition['c_title'] = trim($_GET['c_title']);
        //$condition['search_condition'] = isset($_GET['search_condition'])&&!empty($_GET['search_condition']) ? $_GET['search_condition'] : "";
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
        $condition['leftjoin'] = array('complaints_ext', 'complaints_ext.c_id = complaints.c_id');
        $condition['fields'] = 'complaints.*, complaints_ext.c_id as ct_c_id, complaints_ext.c_desc, complaints_ext.c_img, complaints_ext.c_replay,  complaints_ext.c_mark';
        /*获取投诉列表*/
        $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints'));
        $complaints_list_arr = $dao_complaints->listData($condition);
//print_r($complaints_list_arr);
        $dao_complaints_type = new \MDAO\Complaints(array('table'=>'complaints_type'));
        $complaints_type_list_arr = $dao_complaints_type->listData(array('pager' => 0));

        if (!empty($complaints_list_arr['data']))
        {
            foreach ($complaints_list_arr['data'] as $key => $val)
            {
                $uids[] = $val['c_author'];
                $uids[] = $val['c_against'];
            }
        }
        if (!empty($uids))
        {
            $uids = array_unique($uids);
            /*获取管理员列表*/
            $dao_users = new \MDAO\Users();

            $users_list = $dao_users -> listData(array('pager'=>false,'fields'=>'u_id,u_mobile', 'u_id' => array('type' => 'in', 'value' => $uids)));
            if (!empty($users_list['data']))
            {
                foreach ($complaints_list_arr['data'] as $k => $v)
                {
                    foreach ($users_list['data'] as $key => $val)
                    {
                        if (isset($val['u_id']) && isset($v['c_author']) && $val['u_id'] == $v['c_author']) $complaints_list_arr['data'][$k]['c_author'] = $val['u_mobile'];
                        if (isset($val['u_id']) && isset($v['c_against']) && $val['u_id'] == $v['c_against']) $complaints_list_arr['data'][$k]['c_against'] = $val['u_mobile'];
                    }
                    unset($key, $val);
                    if (!empty($complaints_type_list_arr['data']))
                    {
                        foreach ($complaints_type_list_arr['data'] as $key => $val)
                        {
                            if (isset($val['ct_id']) && isset($v['ct_id']) && $val['ct_id'] == $v['ct_id'])
                            {
                                $complaints_list_arr['data'][$k]['ct_name'] = $val['ct_name'];
                            }
                        }
                    }
                    $complaints_list_arr['data'][$k]['c_imgs'] = array();
                    if (isset($v['c_img']) && trim($v['c_img']) != '')
                    {
                        $complaints_list_arr['data'][$k]['c_imgs'] = explode(',', $v['c_img']);
                    }
                }
            }
        }
//print_r($complaints_list_arr);
        $this->myPager($complaints_list_arr['pager']);

        if(!empty($condition['search_condition'])){
            $this->tpl->assign("search_condition",$condition['search_condition']);
        }
        if(isset($condition['ct_id']) && $condition['ct_id'] > 0){
            $this->tpl->assign("ct_id",$condition['ct_id']);
            $this->tpl->assign("c_status", $condition['c_status']);
        }



        $this->tpl->assign('ct_data',$complaints_type_list_arr['data']);
        $this->tpl->assign("complaints_list_arr",$complaints_list_arr);
        $this->tpl->display("Complaints/index.html");
    }

    public function changeStatus()
    {
       if(isset($_GET['c_id'],$_GET['c_status'])&&!empty($_GET['c_id'])){
            $c_id = intval($_GET['c_id']);
            $c_status = intval($_GET['c_status']);
            $c_last_edit_time = time();
            $c_last_editor = static::$manager_status;

            $dao_complaints = new \MDAO\Complaints(array('table'=>'complaints'));
            $res = $dao_complaints ->updateData(array('c_status'=>$c_status,'c_last_edit_time'=>$c_last_edit_time,'c_last_editor'=>$c_last_editor),array('c_id'=>$c_id));

            if($res){
                echo 1;
            }

       }else{
         echo 0;
       }
    }

    public function extData()
    {
        if (isset($_REQUEST['c_id']) && intval($_REQUEST['c_id']) > 0)
        {
            (isset($_REQUEST['c_mark'])) && $data['c_mark'] = trim($_REQUEST['c_mark']);
            (isset($_REQUEST['c_replay'])) && $data['c_replay'] = trim($_REQUEST['c_replay']);
            $dao_complaints_ext = new \MDAO\Complaints(array('table'=>'complaints_ext'));
            $result = $dao_complaints_ext->updateData($data, array('c_id' => intval($_REQUEST['c_id'])));
            if (!$result)
            {
                echo 0;exit;
            }
            echo 1;exit;
        }
    }



















}
