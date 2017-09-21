<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-16 13:37:26
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-21 11:31:05
 */
namespace App\Controller;

class Users extends \CLASSES\WebBase
{
    private $head_format = '.png';/*头像格式*/
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function Login()
    {
        $phone_number = !empty($_GET['phone_number']) ? intval($_GET['phone_number']) : 0;
        $verify_code = !empty($_GET['verify_code']) ? intval($_GET['verify_code']) : 0;
        if(empty($phone_number) || empty($verify_code))
        {
            $this->exportData( array('msg' => '手机号或验证码不能为空'),0);
        }

        /*获取验证码信息*/
        $dao_verify_code = new \WDAO\Verifies(array('table'=>'verifies'));
        $self_data = $dao_verify_code->listData(array(
            'u_mobile' => $phone_number,
            'fields' => 'code,v_in_time',
            'limit' => 1,
            'pager'=> false,
                ));

        if(empty($self_data['data']['0']['code']) || empty($self_data['data']['0']['v_in_time'])){
            $this->exportData(array('msg'=>'系统错误请联系管理员'),0);
        }
        $time_max = $this ->web_config['verify_code_time'] + $self_data['data']['0']['v_in_time'];
        $time = time();

        if($verify_code != $self_data['data']['0']['code'] || ($time > $time_max))
        {
            $this->exportData(array('msg'=>'验证码不正确或验证码已过有效期'),0);
        }

        /*获取用户信息*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $user_data = $dao_users->listData(array(
            'u_mobile' => $phone_number,
            'pager' => false,
            'fields'=>'u_id,u_name,u_pass,u_status,u_online,u_id,u_sex',
                ));




        if(!empty($user_data['data']['0']['u_id'])){
            /*用户存在*/
            if($user_data['data']['0']['u_status'] < 0){
                $this->exportData(array('msg'=>'用户登录受限,请联系管理员!'),0);
            }
            $data = array();
            $data['u_token'] = $time;
            $data['u_last_edit_time'] = $time;
            $res = $dao_users ->updateData($data,array('u_id'=>$user_data['data']['0']['u_id']));
            $u_img = $this ->web_config['u_img_url'].$user_data['data']['0']['u_id'].$this->head_format;
            if($res){
                $token = $this->createToken($user_data['data']['0']['u_name'],$user_data['data']['0']['u_pass']);
                $this->exportData(array('token'=>$token,'u_img'=>$u_img,'u_online'=>$user_data['data']['0']['u_online'],'u_name'=>$user_data['data']['0']['u_name'],'u_sex'=>$user_data['data']['0']['u_sex'],'u_id'=>$user_data['data']['0']['u_id']),1);
            }


        }else{
            /*用户不存在*/
            $data = array();
            $data['u_name'] = uniqid('u_');
            $data['u_pass'] = '';
            $data['u_mobile'] = $phone_number;
            $data['u_in_time'] = $time;
            $data['u_last_edit_time'] = $time;
            $data['u_token'] = $time;
            $u_id = $dao_users ->addData($data);
            if($u_id){
                $token = $this->createToken($data['u_name'],$data['u_pass']);
                $this->exportData(array('token'=>$token,'u_img'=>$this ->web_config['u_img_url'].'0'.$this->head_format,'u_online'=>'0','u_name'=>$data['u_name'],'u_sex'=>-1,'u_id'=>$u_id),1);
            }

        }



    }

    /*发送短信验证码*/
    public function sendVerifyCode()
    {
        $phone_number = !empty($_GET['phone_number']) ? intval($_GET['phone_number']) : '';
        if(!empty($phone_number))
        {
            $code = mt_rand(99999,999999);
            echo $code;
            /*发送验证码接口*/
            /*存库*/;
            $dao_verify_code = new \WDAO\Verifies(array('table'=>'verifies'));
            $data = array();
            $data['u_mobile'] = $phone_number;
            $data['code'] = $code;
            $data['v_in_time'] = time();
            $res = $dao_verify_code->addData($data);
            if($res){
                $this->exportData(array('msg'=>'短信发送成功'),1);
            }else{
                $this->exportData(array('msg'=>'短信发送失败'),0);
            }

        }else{
            $this->exportData(array('msg'=>'请填写手机号码'),0);
        }
    }

    private function createToken($u_name,$u_pass,$hash='')
    {

        if($hash=='') $hash = time();
        return encyptPassword($u_name.$u_pass).'|'.base64_encode($hash);
    }

    /*收藏任务列表*/
    public function favorateTasks()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $favorate_arr = $dao_favorate -> listData(array('users_favorate.u_id'=>$u_id,'f_type'=>0,'join'=>array('tasks',"tasks.t_id = users_favorate.f_type_id"),'fields'=>'tasks.t_title,tasks.t_amount,tasks.t_duration,tasks.t_author,tasks.t_status','pager'=>false));
        $this->exportData( $favorate_arr,1);
    }
    /*收藏工人列表*/
    public function favorateUsers()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $favorate_arr = $dao_favorate -> listData(array('users_favorate.u_id'=>$u_id,'f_type'=>1,'join'=>array('users',"users.u_id = users_favorate.f_type_id"),'fields'=>'users.u_id,users.u_sex,users.u_online,users.u_start,users.u_worked_num,f_id','pager'=>false));
        /*获取分类数组*/
        $this->exportData( $favorate_arr,1);
    }

    /*用户余额接口*/
    public function usersFunds()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        $dao_funds = new \WDAO\Users_ext_funds(array('table'=>'users_ext_funds'));
        $res = $dao_funds -> infoData(array('fields'=>'u_id,uef_overage,uef_ticket,uef_envelope','key'=>'u_id','val' => $u_id,'pager'=>false));
        $funds_arr = array();
        $funds_arr['data'] = $res;
        $this->exportData( $funds_arr,1);
    }

    /*用户详情*/
    public function usersInfo()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        /*用户地区详情*/
        $dao_ext_info = new \WDAO\Users(array('table'=>'users_ext_info'));
        $ext_info = $dao_ext_info -> infoData(array('fields'=>'uei_info,u_id,uei_province,uei_city,uei_area,uei_address','key'=>'u_id','val' => $u_id,'pager'=>false));

        $user_area_name = '请选择';
        if(!empty($ext_info['uei_city'])){
            $dao_regions = new \WDAO\Users(array('table'=>'regions'));
            $city_name = $dao_regions ->infoData(array('fields'=>'r_id,r_name','key'=>'r_id','val' => $ext_info['uei_city'],'pager'=>false));
            if(!empty($ext_info['uei_city'])){
                $area_name = $dao_regions ->infoData(array('fields'=>'r_id,r_name','key'=>'r_id','val' => $ext_info['uei_area'],'pager'=>false));
                $user_area_name = $city_name['r_name'].$area_name['r_name'];
            }else{
                $user_area_name = $city_name['r_name'];
            }
        }
        $ext_info['user_area_name'] = $user_area_name;
        unset($ext_info['u_id']);


        /*获取用户users表内信息*/
        $dao_funds = new \WDAO\Users(array('table'=>'users'));
        $res = $dao_funds -> infoData(array('fields'=>'u_id,
        u_name,u_mobile,u_phone,u_fax,u_sex,u_in_time,u_online,u_status,u_type,u_task_status,u_skills,u_start,u_credit,u_top,u_recommend,u_jobs_num,u_worked_num,u_high_opinions,u_low_opinions,u_middle_opinions,u_dissensions,u_true_name,u_idcard','key'=>'u_id','val' => $u_id,'pager'=>false));
        $skills_id = array();
        if($res['u_skills'] != 0){
            $skills_id = explode(',',$res['u_skills']);
        }
        $res['u_skills'] = $skills_id;
        $res['area'] = $ext_info;
        $res['u_info'] = $ext_info['uei_info'];
        $u_img_url = $this ->web_config['u_img_url'];
        $res['u_img'] = $this ->web_config['u_img_url'].$res['u_id'].$this->head_format;
        $funds_arr = array();
        $funds_arr['data'] = $res;
        $this->exportData( $funds_arr,1);
    }

    /*用户修改详细信息*/
    public function usersInfoEdit()
    {
        $data_users = array();
        if (!empty(intval($_REQUEST['u_id']))) $u_id= intval($_REQUEST['u_id']);
            if(empty($u_id)) $this->exportData( array('msg'=>'请输入用户id'),0);
        /*users表*/
        if (isset($_REQUEST['u_phone'])) $data_users['u_phone'] = trim($_REQUEST['u_phone']);
        if (isset($_REQUEST['u_fax'])) $data_users['u_fax'] = trim($_REQUEST['u_fax']);
        if (isset($_REQUEST['u_sex'])) $data_users['u_sex'] = intval($_REQUEST['u_sex']);
        if (isset($_REQUEST['u_online'])) $data_users['u_online'] = intval($_REQUEST['u_online']);
        if (isset($_REQUEST['u_true_name'])) $data_users['u_true_name'] = trim($_REQUEST['u_true_name']);
        if (isset($_REQUEST['u_idcard'])) $data_users['u_idcard'] = trim($_REQUEST['u_idcard']);
        if (isset($_REQUEST['u_skills'])) $data_users['u_skills'] = trim($_REQUEST['u_skills']);
        /*修改users表内容*/
        $res = 0;
        if(!empty($data_users)){
            $dao_users = new \WDAO\Users(array('table'=>'users'));
            $res_users = $dao_users ->updateData($data_users,array('u_id'=>$u_id));
            if(!$res_users){
                $res++;
            }
        }

        /*users_ext_info*/
        $data_ext = array();
        if (isset($_REQUEST['uei_info'])) $data_ext['uei_info'] = deepAddslashes(trim($_REQUEST['uei_info']));
        if (isset($_REQUEST['uei_address'])) $data_ext['uei_address'] = deepAddslashes(trim($_REQUEST['uei_address']));
        if (isset($_REQUEST['uei_zip'])) $data_ext['uei_zip'] = trim($_REQUEST['uei_zip']);
        if (isset($_REQUEST['uei_province'])) $data_ext['uei_province'] = intval($_REQUEST['uei_province']);
        if (isset($_REQUEST['uei_city'])) $data_ext['uei_city'] = intval($_REQUEST['uei_city']);
        if (isset($_REQUEST['uei_area'])) $data_ext['uei_area'] = intval($_REQUEST['uei_area']);
        if(strlen($data_ext['uei_zip']) > 6) $this->exportData( array('msg'=>'邮编的最大字符长度为6'),0);

        if(!empty($data_ext)){
            $dao_users_ext = new \WDAO\Users(array('table'=>'users_ext_info'));
            $ext_u_id = $dao_users_ext -> infoData(array('key'=>'u_id','val'=>$u_id,'fields'=>'u_id','pager'=>false));
            if(!empty($ext_u_id)){
                $res_ext = $dao_users_ext ->updateData($data_ext,array('u_id'=>$u_id));
            }else{
                $data_ext['u_id']= intval($_REQUEST['u_id']);
                $res_ext = $dao_users_ext ->addData($data_ext);
            }

            if(!$res_ext){
                $res++;
            }
        }

        if($res > 0){
            $this->exportData( array('msg'=>'用户信息修改失败'),0);
        }else{
            $this->exportData( array('msg'=>'用户信息修改成功'),1);
        }


    }

}

