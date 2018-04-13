<?php
/**
 * 收藏接口
 */
namespace App\Controller;

class Favorate extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }

    /*收藏任务列表*/
    public function favorateTasks()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $favorate_arr = $dao_favorate -> listData(array('users_favorate.u_id'=>$u_id,'f_type'=>0,'leftjoin'=>array('task_ext_info','task_ext_info.t_id = users_favorate.f_type_id'),'join'=>array('tasks',"tasks.t_id = users_favorate.f_type_id"),'fields'=>'tasks.t_title,tasks.t_amount,tasks.t_duration,tasks.t_id,tasks.t_author,tasks.t_status,f_id,task_ext_info.t_desc','pager'=>false));
        foreach ($favorate_arr['data'] as $key => &$value) {
            if(!empty($value['t_author'])){
                $value['u_img'] = $this-> getHeadById($value['t_author']);
            }
        }
        $this->exportData( $favorate_arr,1);
    }

   /*收藏工人列表*/
    public function favorateUsers()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }
        // if(empty($_GET['ucp_posit_x']) || empty($ucp_posit_x = floatval($_GET['ucp_posit_x']))){
        //     $this->exportData( array('msg'=>'用户x坐标为空'),0);
        // }
        // if(empty($_GET['ucp_posit_y']) || empty($ucp_posit_y = floatval($_GET['ucp_posit_y']))){
        //     $this->exportData( array('msg'=>'用户y坐标为空'),0);
        // }


        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        /*获取当前userid收藏的用户id*/
        $favorate_id_arr = array();
        $favorate_arr = $dao_favorate -> listData(array(
            'users_favorate.u_id'=>$u_id,
            'f_type'=>1,
            'fields'=>'f_type_id,f_id','pager'=>false,
            ));
        if(empty($favorate_arr['data'])){
            $this->exportData( array('data'=>array()),1);
        }
        foreach ($favorate_arr['data'] as $key => $value) {
            $favorate_id_arr[] = $value['f_type_id'];
        }

        /*获取用户的个人信息和用户自我介绍数组*/
        $dao_users = new \WDAO\Users_favorate(array('table'=>'users'));
        $users_arr = $dao_users -> listData(array(
            'u_id' => array('type' => 'in', 'value' => $favorate_id_arr),
            'fields'=>'u_id,u_task_status,users.u_true_name as u_name,u_sex','pager'=>false,
            )
        );
        $dao_users_ext_info = new \WDAO\Users_favorate(array('table'=>'users_ext_info'));
        $users_ext_arr = $dao_users_ext_info -> listData(array(
            'u_id' => array('type' => 'in', 'value' => $favorate_id_arr),
            'fields'=>'u_id,uei_info','pager'=>false,
            )
        );
        $dao_users_cur_position = new \WDAO\Users_favorate(array('table'=>'users_cur_position'));
        $users_position_arr = $dao_users_cur_position -> listData(array(
            'u_id' => array('type' => 'in', 'value' => $favorate_id_arr),
            'fields'=>'u_id,ucp_posit_x,ucp_posit_y','pager'=>false,
            )
        );
        $res = array();
        foreach ($favorate_id_arr as $key => $value) {
            $res[$key]['u_id'] = $value;
            $res[$key]['u_img'] = $this-> getHeadById($value);
            foreach ($users_arr['data'] as $k_user => $v_user) {
                if($value == $v_user['u_id']){
                    $res[$key]['u_name'] = isset($v_user['u_name']) ? $v_user['u_name'] : '';
                    $res[$key]['u_task_status'] = isset($v_user['u_task_status']) ? $v_user['u_task_status'] : '';
                    $res[$key]['u_sex'] = isset($v_user['u_sex']) ? $v_user['u_sex'] : '';
                }
            }
            foreach ($users_ext_arr['data'] as $k_ext => $v_ext) {
                if($value == $v_ext['u_id']){
                    $res[$key]['uei_info'] = isset($v_ext['uei_info']) ? $v_ext['uei_info'] : '';
                }
            }
            foreach ($users_position_arr['data'] as $k_position => $v_position) {
                if($value == $v_position['u_id']){
                    if(!empty($v_position['ucp_posit_x']) && !empty($v_position['ucp_posit_y'])){
                        // $res[$key]['distance'] = $this -> GetDistance($v_position['ucp_posit_x'],$v_position['ucp_posit_y'],$ucp_posit_x,$ucp_posit_y);
                        $res[$key]['ucp_posit_x'] = $v_position['ucp_posit_x'];
                        $res[$key]['ucp_posit_y'] = $v_position['ucp_posit_y'];
                    }
                }
            }
            foreach ($favorate_arr['data'] as $k => $v) {
                if($value == $v['f_type_id']){
                    $res[$key]['f_id'] = isset($v['f_id']) ? $v['f_id'] : '';
                }
            }
        }
        /*获取分类数组*/
        $this->exportData( array('data'=>$res),1);
    }

    /*收藏删除*/
    public function favorateDel()
    {
        if (empty($_GET['f_id']) || empty($f_id = intval($_GET['f_id']))){
             $this->exportData( array('msg'=>'请输入被收藏id'),0);
        }
        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));

        $res = $dao_favorate ->delData(array('f_id'=>array('type'=>'in','value'=>$f_id)));
        if($res){
            $this->exportData( array('msg'=>'取消收藏成功'),1);
        }


    }
    /*收藏添加*/
    public function favorateAdd()
    {
        $data = array();
        if (empty($_GET['u_id']) || empty($data['u_id'] = intval($_GET['u_id']))){
             $this->exportData( array('msg'=>'请输入被收藏id'),0);
        }
        if (empty($_GET['f_type_id']) || empty($data['f_type_id'] = intval($_GET['f_type_id']))){
             $this->exportData( array('msg'=>'请输入被收藏id'),0);
        }
        if (!isset($_GET['f_type'])){
             $this->exportData( array('msg'=>'请输入收藏类型'),0);
        }
        if($_GET['f_type'] == 1 && $data['u_id'] == $data['f_type_id']){
            $this->exportData( array('msg'=>'您不能收藏自己'),0);
        }
        $data['f_type'] = intval($_GET['f_type']);
        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $f_id = $dao_favorate ->addData($data);
        if(intval($f_id) > 0){
            $this->exportData(array('data'=>array('f_id'=>$f_id)),1);
        }
    }

}