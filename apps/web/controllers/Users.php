<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-16 13:37:26
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-10-12 12:07:48
 */
namespace App\Controller;

class Users extends \CLASSES\WebBase
{
    private $head_format = '.jpg';/*头像格式*/
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
            'fields'=>'u_name,u_pass,u_status,u_online,u_id,u_sex',
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
            $u_img = $this-> getHeadById($user_data['data']['0']['u_id']);
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

            /*发送验证码接口*/
            $content = '用户您好,您的登录验证码为'.$code.'。';
            $result = sendSms($phone_number, $content);
            if(!$result)
            {
                $this->exportData(array('msg'=>'短信发送失败'),0);
            }
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
        $favorate_arr = $dao_favorate -> listData(array('users_favorate.u_id'=>$u_id,'f_type'=>0,'join'=>array('tasks',"tasks.t_id = users_favorate.f_type_id"),'fields'=>'tasks.t_title,tasks.t_amount,tasks.t_duration,tasks.t_author,tasks.t_status,f_id','pager'=>false));
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
            $this->exportData( $favorate_id_arr,1);
        }
        foreach ($favorate_arr['data'] as $key => $value) {
            $favorate_id_arr[] = $value['f_type_id'];
        }

        /*获取用户的个人信息和用户自我介绍数组*/
        $dao_users = new \WDAO\Users_favorate(array('table'=>'users'));
        $users_arr = $dao_users -> listData(array(
            'u_id' => array('type' => 'in', 'value' => $favorate_id_arr),
            'fields'=>'u_id,u_task_status,u_name,u_sex','pager'=>false,
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
        if (empty($_GET['f_type']) ||  empty($data['f_type'] = intval($_GET['f_type']))){
             $this->exportData( array('msg'=>'请输入收藏类型'),0);
        }
        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $f_id = $dao_favorate ->addData($data);
        if(intval($f_id) > 0){
            $this->exportData(array('data'=>array('f_id'=>$f_id)),1);
        }
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
        // $skills_id = array();
        // if($res['u_skills'] != 0){
        //     $skills_id = explode(',',$res['u_skills']);
        // }
        // $res['u_skills'] = $skills_id;

        $res['u_info'] = isset($ext_info['uei_info']) ? $ext_info['uei_info'] : '';
        unset($ext_info['uei_info']);
        $res['area'] = $ext_info;
        $u_img_url = $this ->web_config['u_img_url'];
        $res['u_img'] = $this-> getHeadById($res['u_id']);
        $funds_arr = array();
        $funds_arr['data'] = $res;
        $this->exportData( $funds_arr,1);
    }

    /*用户修改详细信息*/
    public function usersInfoEdit()
    {
        $data_users = array();
        if (empty($_POST['u_id']) || intval($_POST['u_id']) == 0){
             $this->exportData( array('msg'=>'请输入用户id'),0);
        }
        $u_id= intval($_POST['u_id']);
        /*users表*/
        if (isset($_POST['u_phone'])) $data_users['u_phone'] = trim($_POST['u_phone']);
        if (isset($_POST['u_fax'])) $data_users['u_fax'] = trim($_POST['u_fax']);
        if (isset($_POST['u_sex'])) $data_users['u_sex'] = intval($_POST['u_sex']);
        if (isset($_POST['u_online'])) $data_users['u_online'] = intval($_POST['u_online']);
        if (isset($_POST['u_true_name'])) $data_users['u_true_name'] = trim($_POST['u_true_name']);
        if (isset($_POST['u_idcard'])) $data_users['u_idcard'] = trim($_POST['u_idcard']);
        if (isset($_POST['u_skills'])) $data_users['u_skills'] = trim(','.$_POST['u_skills'].',');
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
        if (isset($_POST['uei_info'])) $data_ext['uei_info'] = deepAddslashes(trim($_POST['uei_info']));
        if (isset($_POST['uei_address'])) $data_ext['uei_address'] = deepAddslashes(trim($_POST['uei_address']));
        if (isset($_POST['uei_zip'])) $data_ext['uei_zip'] = trim($_POST['uei_zip']);
        if (isset($_POST['uei_province'])) $data_ext['uei_province'] = intval($_POST['uei_province']);
        if (isset($_POST['uei_city'])) $data_ext['uei_city'] = intval($_POST['uei_city']);
        if (isset($_POST['uei_area'])) $data_ext['uei_area'] = intval($_POST['uei_area']);
        if(isset($data_ext['uei_zip']) && strlen($data_ext['uei_zip']) > 6) $this->exportData( array('msg'=>'邮编的最大字符长度为6'),0);
        if(isset($data_ext['uei_info']) && mb_strlen($data_ext['uei_info'],'utf8') > 250) $this->exportData( array('msg'=>'个人简介的最大字符长度为250'),0);
        if(isset($data_ext['uei_address']) && mb_strlen($data_ext['uei_address'],'utf8') > 75) $this->exportData( array('msg'=>'个人简介的最大字符长度为75'),0);

        if(!empty($data_ext)){
            $dao_users_ext = new \WDAO\Users(array('table'=>'users_ext_info'));
            $ext_u_id = $dao_users_ext -> infoData(array('key'=>'u_id','val'=>$u_id,'fields'=>'u_id','pager'=>false));
            if(!empty($ext_u_id)){
                $res_ext = $dao_users_ext ->updateData($data_ext,array('u_id'=>$u_id));
            }else{
                $data_ext['u_id']= intval($_POST['u_id']);
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

    /*工种id查工人接口*/
    /**
     * [getUsersBySkills description] 工种id查工人接口
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-09-25
     * @return [type]   工人信息         [description]
     *
     */
    public function getUsers()
    {

        $data = array();
        if (isset($_REQUEST['u_id']) && intval($_REQUEST['u_id']) > 0) $data['u_id'] = intval($_REQUEST['u_id']);
        if(isset($_REQUEST['u_mobile']) && intval($_REQUEST['u_mobile']) > 0) $data['u_mobile'] = intval($_REQUEST['u_mobile']);
        if(isset($_REQUEST['u_sex']) && intval($_REQUEST['u_sex']) > 0) $data['u_sex'] = intval($_REQUEST['u_sex']);
        if(isset($_REQUEST['u_bind_mobile']) && intval($_REQUEST['u_bind_mobile']) > 0) $data['u_bind_mobile'] = intval($_REQUEST['u_bind_mobile']);
        if(isset($_REQUEST['u_online']) && intval($_REQUEST['u_online']) > 0) $data['u_online'] = intval($_REQUEST['u_online']);
        if(isset($_REQUEST['u_status']) && intval($_REQUEST['u_status']) > 0) $data['u_status'] = intval($_REQUEST['u_status']);
        if(isset($_REQUEST['u_type']) && intval($_REQUEST['u_type']) > 0) $data['u_type'] = intval($_REQUEST['u_type']);
        if(isset($_REQUEST['u_task_status']) && intval($_REQUEST['u_task_status']) >=0) $data['u_task_status'] = intval($_REQUEST['u_task_status']);
        if(isset($_REQUEST['u_idcard']) && intval($_REQUEST['u_idcard']) > 0) $data['u_idcard'] = intval($_REQUEST['u_idcard']);
        if(isset($_REQUEST['u_true_name']) && trim($_REQUEST['u_true_name'])) $data['u_true_name'] = trim($_REQUEST['u_true_name']);
        if(isset($_REQUEST['u_skills']) && trim($_REQUEST['u_skills'])) $data['u_skills'] = trim($_REQUEST['u_skills']);
        if(isset($_REQUEST['u_name']) && trim($_REQUEST['u_name'])) $data['u_name'] = trim($_REQUEST['u_name']);

        /*区间值*/

        if (isset($_REQUEST['start_time'])) $data['u_in_time'][0] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_time']));
        if (isset($_REQUEST['end_time'])) $data['u_in_time'][1] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_time']));
        if (isset($_REQUEST['start_credit'])) $data['u_credit'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_credit']));
        if (isset($_REQUEST['end_credit'])) $data['u_credit'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_credit']));
        if (isset($_REQUEST['start_jobs'])) $data['u_jobs_num'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_jobs']));
        if (isset($_REQUEST['end_jobs'])) $data['u_jobs_num'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_jobs']));
        if (isset($_REQUEST['start_worked'])) $data['u_worked_num'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_worked']));
        if (isset($_REQUEST['end_worked'])) $data['u_worked_num'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_worked']));
        if (isset($_REQUEST['start_high'])) $data['u_high_opinions'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_high']));
        if (isset($_REQUEST['end_high'])) $data['u_high_opinions'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_high']));
        if (isset($_REQUEST['start_low'])) $data['u_low_opinions'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_low']));
        if (isset($_REQUEST['end_low'])) $data['u_low_opinions'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_low']));
        if (isset($_REQUEST['start_middle'])) $data['u_middle_opinions'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_middle']));
        if (isset($_REQUEST['end_middle'])) $data['u_middle_opinions'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_middle']));
        if (isset($_REQUEST['start_dissensions'])) $data['u_dissensions'][0] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_dissensions']));
        if (isset($_REQUEST['end_dissensions'])) $data['u_dissensions'][1] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_dissensions']));

    /*users_ext_info*/
        if(isset($_REQUEST['uei_province']) && intval($_REQUEST['uei_province']) > 0) $data2['uei_province'] = intval($_REQUEST['uei_province']);
        if(isset($_REQUEST['uei_city']) && intval($_REQUEST['uei_city']) > 0) $data2['uei_city'] = intval($_REQUEST['uei_city']);
        if(isset($_REQUEST['uei_area']) && intval($_REQUEST['uei_area']) > 0) $data2['uei_area'] = intval($_REQUEST['uei_area']);
        if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $data['page'] = intval($_REQUEST['page']);
        $data2['pager'] = 0;
        if (isset($data['page']) && intval($data['page']) > 0)
        {
            $data2['pager'] = 1;
        }
        $dao_info = new \WDAO\Users_ext_info();
        $data2['rightjoin'] = array('users', 'users_ext_info.u_id = users.u_id');
        $data2['where'] = ' users.u_id > 0 ';
        if (!empty($data))
        {
            foreach ($data as $key => $val)
            {
                if ($key == 'u_name')
                {
                    $data2['where'] .= ' and users.u_name like "%'.$val.'%"';
                }
                elseif($key == 'u_skills')
                {
                    $data2['where'] .= ' and FIND_IN_SET('.$val.', users.u_skills)';
                }
                elseif (in_array($key, array('u_in_time', 'u_credit', 'u_jobs_num', 'u_worked_num', 'u_high_opinions', 'u_low_opinions', 'u_middle_opinions', 'u_dissensions')))
                {
                    if (isset($data[$key][0]) && !empty($data[$key][0]))
                    {
                        $data2['where'] .= ' and ' . $key . ' <= ' . $val[0]['ge_value'];
                    }

                    if (isset($data[$key][1]) && !empty($data[$key][1]))
                    {
                        $data2['where'] .= ' and ' . $key . ' >= ' . $val[1]['le_value'];
                    }
                }
                else
                {
                    $data2['where'] .= ' and users.' . $key . ' = ' . $val;
                }
            }
        }

    /*users_cur_posit*/
        if (isset($_REQUEST['start_x'])) $data3['ucp_posit_x'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['start_x']));
        if (isset($_REQUEST['end_x'])) $data3['ucp_posit_x'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['end_x']));
        if (isset($_REQUEST['start_y'])) $data3['ucp_posit_y'][0] = array('type' => 'ge', 'ge_value' => floatval($_REQUEST['start_y']));
        if (isset($_REQUEST['end_y'])) $data3['ucp_posit_y'][1] = array('type' => 'le', 'le_value' => floatval($_REQUEST['end_y']));
        if (isset($_REQUEST['distance'])) $data3['distance'] =  intval($_REQUEST['distance']);

        $data2['leftjoin'] = array('users_cur_position', 'users_cur_position.u_id = users_ext_info.u_id');
        if(isset($data3['ucp_posit_x']) || isset($data3['ucp_posit_y'])){
            foreach ($data3 as $key => $val){
                if (isset($data3[$key][0]) && !empty($data3[$key][0]))
                {
                    $data2['where'] .= ' and ' . $key . ' >= ' . $val[0]['ge_value'];
                }
                if (isset($data3[$key][1]) && !empty($data3[$key][1]))
                {
                    $data2['where'] .= ' and ' . $key . ' <= ' . $val[1]['le_value'];
                }
            }
        }
        $data2['fields'] = 'users.u_id,u_name,u_skills,users_ext_info.uei_info,u_task_status,u_true_name,ucp_posit_x,ucp_posit_y';

        $list = $dao_info ->listData($data2);
        foreach ($list['data'] as  &$v) {
            if(isset($v['u_id'])){
               $v['u_img'] = $this-> getHeadById($v['u_id']);
            }
        }
        $this->exportData($list,1);
    }


    /*获取用户头像信息*/
    private function getHeadById($u_id = 0)
    {
        if(!is_dir($this ->web_config['u_img_path'])){
            $res = mkdir($this ->web_config['u_img_path'],0777,true);
            if(!$res){
                return '';
            }
        }
        if(file_exists($this ->web_config['u_img_path'].$u_id.$this->head_format)){
            return $this ->web_config['u_img_url'].$u_id.$this->head_format;
        }else{
            return $this ->web_config['u_img_url'].'0'.$this->head_format;
        }
    }

    /*获取用户资金日志*/
    public function getUsersFundsLog()
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户id不能为空'),0);
        }
        $page = !empty($_GET['page']) && !empty(intval($_GET['page'])) ? intval($_GET['page']) :1;
        $category = !empty($_GET['category'])  ? trim($_GET['category']) : 'all';
        /*充值*/
        $recharge_list['data'] = '';
        $withdraw_list['data'] = '';
        if($category=='all' || $category=='recharge'){
        $dao_recharge_log = new \WDAO\Users(array('table'=>'user_recharge_log'));
        $recharge_list = $dao_recharge_log ->listData(array('u_id'=>$u_id,'page'=>$page, 'fields'=>'url_amount,url_id,p_id,url_in_time,url_status,url_solut_time,url_card'));
        }

        /*提现*/
        if($category=='all' || $category=='withdraw'){
        $dao_withdraw_log = new \WDAO\Users(array('table'=>'user_withdraw_log'));
        $withdraw_list = $dao_withdraw_log ->listData(array('u_id'=>$u_id,'page'=>$page, 'fields'=>'uwl_id,uwl_amount,uwl_in_time,uwl_status,uwl_solut_time,uwl_card,p_id'));
        }


        $this->exportData( array('recharge_list'=>$recharge_list['data'],'withdraw_list'=>$withdraw_list['data']),1);

    }

    /*获取2点之间的距离*/
    public function GetDistance($lat1, $lng1, $lat2, $lng2)
    {
        $radLat1 = $lat1 * (PI / 180);
        $radLat2 = $lat2 * (PI / 180);
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * (PI / 180)) - ($lng2 * (PI / 180));
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s * EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return $s;
    }


    /*头像修改类*/
    public function usersHeadEidt()
    {
        $filename = uniqid().'.jpg';/*临时文件名*/
        $content = file_get_contents("php://input");/*接收app传过来的文件*/

        /*截掉post过来的key*/
        $param1 = substr($content, 7);
        /*这里是转码 Unicode转Native*/
        $param2 = str_replace(" ","+",$param1);
        $param2 = str_replace("%2F","/",$param2);
        $param2 = str_replace("%2B","+",$param2);
        $param2 = str_replace("%0A","",$param2);

        $content = base64_decode($param2); // 将格式为base64的字符串解码


        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户id不能为空'),0);
        }
        if( !isset($_GET['img_name']) || empty($img_name = trim($_GET['img_name']))){
            $this->exportData( array('msg'=>'用户头像图片名不能为空'),0);
        }
        $ext = strrchr($img_name,'.');

        $img_type = array('.jpg','.jpeg','.png','.gif');
        if( !in_array($ext,$img_type)){
            $this->exportData( array('msg'=>'只支持图像格式为jpg,png,git,jpeg格式的图像'),0);
        }

        /*如果文件写入成功*/
        if(!is_dir($this ->web_config['u_img_path'])){
            $res = mkdir($this ->web_config['u_img_path'],0777,true);
            if(!$res){
               $this->exportData( array('msg'=>'图片目录创建失败'),0);
            }
        }
        if(!empty($content)){
            if (file_put_contents($this ->web_config['u_img_path'].$filename, $content))
            {
                $imageInfo = getimagesize ($this ->web_config['u_img_path'].$filename);/*验证图片*/
                if ($imageInfo == false) {
                    unlink($this ->web_config['u_img_path'].$filename);
                    $this->exportData( array('msg'=>'非法上传'),0);
                }
                \Swoole\Image::thumbnail($this ->web_config['u_img_path'].$filename,
                            $this ->web_config['u_img_path'].$u_id.'.jpg',
                            $this->web_config['u_img_w'],
                            $this->web_config['u_img_h'],
                            1000);
                unlink($this ->web_config['u_img_path'].$filename);
                $this->exportData( array('msg'=>'头像修改成功'),1);

            }else{
                $this->exportData( array('msg'=>'头像写入失败'),0);
            }
        }else{

            $this->exportData( array('msg'=>'您没有上传图片'),0);
        }


    }

    /*用户站内标题信息*/
    public function msgList($value='')
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户id不能为空'),0);
        }

        $page = isset($_GET['page']) && !empty(intval($_GET['page'])) ? intval($_GET['page']) : 1;

        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $msg_list = $dao_user_msg ->listData(array(
            'u_id' => $u_id,
            'pager' => true,
            'page' => $page,
            'web_msg.wm_status' => 1,
            'where' => 'um_status != -1',
            'fields' => 'wm_title,wm_in_time,wm_type,user_msg.wm_id,um_id',
            'join' => array('web_msg','web_msg.wm_id=user_msg.wm_id '),
            ));
        unset($msg_list['pager']);
        $this->exportData( $msg_list,1);
    }

    /*删除站内信息*/
    public function msgDel()
    {

        if(empty($_GET['um_id']) || empty($um_id = intval($_GET['um_id']))){
            $this->exportData( array('msg'=>'用户站内信关系ID为空'),0);
        }

        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $res = $dao_user_msg ->updateData(array(
            'um_status' => '-1',
            ),array('um_id'=>$um_id));
        if($res){
            $this->exportData( array('msg'=>'信息删除成功'),1);
        }else{
            $this->exportData( array('msg'=>'信息删除失败'),0);
        }


    }

    /*站内信详细信息*/
    public function msgInfo()
    {

        if(empty($_GET['um_id']) || empty($um_id = intval($_GET['um_id']))){
            $this->exportData( array('msg'=>'用户站内信关系ID为空'),0);
        }
        /*修改状态*/
        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $msg_list = $dao_user_msg ->updateData(array(
            'um_status' => '1',
            ),array('um_id'=>$um_id));
        $wm_id = 0;
        $wm_id_arr = $dao_user_msg ->infoData(array('key'=>'um_id','val'=>$um_id,'fields'=>'wm_id,um_id'));
        if($wm_id_arr['wm_id']){
            $wm_id = $wm_id_arr['wm_id'];
        }

        /*获取内容*/
        $info = array();
        if(!empty($wm_id)){
            $dao_web_msg = new \WDAO\Users(array('table'=>'web_msg'));

            $info = $dao_web_msg -> listData(array(
                'where' => 'web_msg.wm_id='.$wm_id,
                'wm_status' => 1,
                'fields'=>'wm_title,wm_in_time,wm_desc,web_msg_ext.wm_id',
                'join' => array('web_msg_ext','web_msg.wm_id=web_msg_ext.wm_id '),
                ));
            unset($info['pager']);

        }
        $this->exportData( $info,1);
    }


    /*前台配置文件接口*/
    public function getAppConfig()
    {
        $application_config = array();
        if (file_exists(WEBPATH . '/configs/application_config.php')){
            require WEBPATH . '/configs/application_config.php';
        }else{
            $dao_application_config = new \WDAO\Users(array('table'=>'application_config'));
            $data = $dao_application_config ->listData(array('pager'=>false,'fields'=>'ac_name,ac_value','ac_status'=>1));

            $res = array();
            foreach ($data['data'] as  $v) {
                $res["{$v['ac_name']}"] = $v['ac_value'];
            }
            file_put_contents(WEBPATH . '/configs/application_config.php','<?php $application_config='.var_export($res,true).'?>');
            if (file_exists(WEBPATH . '/configs/application_config.php')){
                require WEBPATH . '/configs/application_config.php';
            }else{
                $this->exportData(0,array('msg'=>'系统错误请联系管理员'));
            }
        }
        $this->exportData( array('data' => $application_config),1);

    }

    /*用户位置信息修改*/
    public function updatePosition()
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_GET['ucp_posit_x']) || empty($ucp_posit_x = floatval($_GET['ucp_posit_x']))){
            $this->exportData( array('msg'=>'用户x轴信息为空'),0);
        }
        if(empty($_GET['ucp_posit_y']) || empty($ucp_posit_y = floatval($_GET['ucp_posit_y']))){
            $this->exportData( array('msg'=>'用户y轴信息为空'),0);
        }

        $users_info = array();
        $users_info['ucp_posit_x'] = $ucp_posit_x;
        $users_info['ucp_posit_y'] = $ucp_posit_y;
        $users_info['ucp_last_edit_time'] = time();

        $dao_cur_position = new \WDAO\Users(array('table'=>'users_cur_position'));
        $u_id_arr = $dao_cur_position ->infoData(array('key'=>'u_id','val'=>$u_id,'fields'=>'u_id'));

        $res = false;
        if(!empty($u_id_arr['u_id'])){
            $res = $dao_cur_position ->updateData($users_info,array('u_id'=>$u_id));
        }else{
            $users_info['u_id'] = $u_id;
            $res = $dao_cur_position ->addData($users_info);
        }

        if($res){
            $this->exportData( array('msg'=>'位置信息修改成功'),1);
        }else{
            $this->exportData( array('msg'=>'位置信息修改失败'),0);
        }
    }

    /*用户投诉信息问题提示信息*/
    public function complaintsType()
    {
        $ct_type = isset($_GET['ct_type']) && (!empty(intval($_GET['ct_type'])) || $_GET['ct_type'] === '0') ?  intval($_GET['ct_type']) : -1;
        $condition = array();
        $condition['ct_status'] = 1;
        $condition['fields'] = 'ct_id,ct_name';
        if($ct_type !== -1){
            $condition['ct_type'] = $ct_type;
        }

        $complaints_type = new \WDAO\Users(array('table'=>'complaints_type'));
        $complaints_type_arr = $complaints_type -> listData($condition);
        unset($complaints_type_arr['pager']);
        $this->exportData( array($complaints_type_arr),1);
    }


    /*添加投诉信息*/
    public function complaintsAdd()
    {

        if(empty($_POST['c_id']) || empty(intval($_POST['c_id']))){
            if(empty($_POST['c_author']) || empty($c_author = intval($_POST['c_author']))){
                $this->exportData( array('msg'=>'用户ID为空'),0);
            }
            if(empty($_POST['c_against']) || empty($c_against = intval($_POST['c_against']))){
                $this->exportData( array('msg'=>'针对投诉人不能为空'),0);
            }
            if(empty($_POST['ct_id']) || empty($ct_id = intval($_POST['ct_id']))){
                $this->exportData( array('msg'=>'投诉类型不能为空'),0);
            }
            $data = array();
            !empty($_POST['c_title']) ? $array['c_title'] = trim($_POST['c_title']) : false;
            $data['c_author'] = $c_author;
            $data['c_against'] = $c_against;
            $data['ct_id'] = $ct_id;
            $data['c_in_time'] = time();
            $dao_complaints = new \WDAO\Users(array('table'=>'complaints'));

            $c_id = 0;
            $c_id = $dao_complaints ->addData($data);

            if($c_id <= 0) {
                $this->exportData( array('msg'=>'投诉信息写入失败'),0);
            }


            if(!empty($c_id)){
                $ext_data = array();
                $ext_data['c_id'] = $c_id;
                $ext_data['c_replay'] = '';
                $ext_data['c_mark'] = '';
                $ext_data['c_desc'] = isset($_POST['c_desc']) ? trim($_POST['c_desc']) : ' ';
                if(!empty($_POST['c_img'])){
                    $ext_data['c_img'] = $dao_complaints ->uploadComplaintImg($_POST['c_img'],'../uploads/images/'.date('Y/m/d'));
                }
                $dao_complaints_ext = new \WDAO\Users(array('table'=>'complaints_ext'));
                $res_ext_add = $dao_complaints_ext -> addData($ext_data);
                if($res_ext_add){
                    $this->exportData( array('msg'=>'投诉信息写入成功','c_id'=>$c_id),1);
                }

            }
        }else{
            if(isset($_POST['c_id']) && !empty(intval($_POST['c_id'])) && !empty($_POST['c_img'])){
                $dao_complaints_ext = new \WDAO\Users(array('table'=>'complaints_ext'));
                $complaints_ext_info = $dao_complaints_ext -> infoData(array(
                    'fields' => 'c_img,c_id',
                    'key' => 'c_id',
                    'val' => $_POST['c_id'],
                    ));
                $ext_data = '';
                $img_path = $dao_complaints_ext ->uploadComplaintImg($_POST['c_img'],'../uploads/images/'.date('Y/m/d'));

                if(!empty($img_path)){
                    if(!empty($complaints_ext_info['c_img'])){
                        $ext_data = array('c_img'=>$complaints_ext_info['c_img'].','.$img_path);
                    }else{
                        $ext_data = array('c_img'=>$img_path);
                    }
                    $res = $dao_complaints_ext ->updateData($ext_data,array('c_id'=>$_POST['c_id']));
                    if($res){
                        $this->exportData( array('msg'=>'图片信息修改成功'),1);
                    }else{
                        $this->exportData( array('msg'=>'图片信息修改失败'),0);
                    }
                }else{
                    $this->exportData( array('msg'=>'图片信息写入失败'),0);
                }

            }
        }
    }

    /*用户提现申请接口*/
    public function applyWithdraw()
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_GET['uwl_amount']) || empty($uwl_amount = intval($_GET['uwl_amount']))){
            $this->exportData( array('msg'=>'提现金额不能为空'),0);
        }
        if(empty($_GET['p_id']) || empty($p_id = intval($_GET['p_id']))){
            $this->exportData( array('msg'=>'提现方式不能为空'),0);
        }
        if(empty($_GET['uwl_card']) || empty($uwl_card = intval($_GET['uwl_card']))){
            $this->exportData( array('msg'=>'提现账号不能为空'),0);
        }
        if(empty($_GET['uwl_truename']) || empty($uwl_truename = trim($_GET['uwl_truename']))){
            $this->exportData( array('msg'=>'提现账号姓名不能为空'),0);
        }
        $dao_user_withdraw_log = new \WDAO\Users(array('table'=>'user_withdraw_log'));
        $dao_users_ext_funds = new \WDAO\Users(array('table'=>'users_ext_funds'));
        /*判断用户余额是否大于提现余额*/
        $uef_overage = $dao_users_ext_funds ->infoData(array('fields'=>'u_id,uef_overage','key'=>'u_id','val' => $u_id,'pager'=>false));
        if(!empty($uef_overage['uef_overage'])){
            if(intval($uef_overage['uef_overage']) >= $uwl_amount){
                /*处理提现过程start*/

                /*用户余额修改*/
                $funds_res = $this ->userFunds($u_id, $uwl_amount*-1, $type = 'withdraw');
                if(!$funds_res){
                    $this->exportData( array('msg'=>'提现申请失败'),0);
                }
                /*用户提现申请日志*/
                $WD_log_id = $this ->usersWithdrawLog($u_id, $uwl_amount, $uwl_truename, $uwl_card, $status = 0, $p_id);
                /*平台资金流向修改*/
                $dao_platform_funds_log = new \WDAO\Users(array('table'=>'platform_funds_log'));
                $data = array();
                $data['pfl_type'] = 1;
                $data['pfl_type_id'] = $WD_log_id;
                $data['pfl_amount'] = $uwl_amount;
                $data['pfl_in_time'] = time();
                $data['pfl_reason'] = 'withdraw';
                $data['pfl_status'] = 1;
                $dao_platform_funds_log -> addData($data);

                /*处理提现过程end*/
            }else{
                $this->exportData( array('msg'=>'用户余额不足'),0);
            }
        }else{
            $this->exportData( array('msg'=>'用户余额不足'),0);
        }
    }

        /*充值记录接口*/
        public function applyRechargeLog()
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_GET['uwl_amount']) || empty($uwl_amount = intval($_GET['uwl_amount']))){
            $this->exportData( array('msg'=>'提现金额不能为空'),0);
        }
        if(empty($_GET['p_id']) || empty($p_id = intval($_GET['p_id']))){
            $this->exportData( array('msg'=>'提现方式不能为空'),0);
        }
        if(empty($_GET['uwl_card']) || empty($uwl_card = intval($_GET['uwl_card']))){
            $this->exportData( array('msg'=>'提现账号不能为空'),0);
        }
        if(empty($_GET['uwl_truename']) || empty($uwl_truename = trim($_GET['uwl_truename']))){
            $this->exportData( array('msg'=>'提现账号姓名不能为空'),0);
        }
        $dao_user_withdraw_log = new \WDAO\Users(array('table'=>'user_withdraw_log'));
        $dao_users_ext_funds = new \WDAO\Users(array('table'=>'users_ext_funds'));
        /*判断用户余额是否大于提现余额*/
        $uef_overage = $dao_users_ext_funds ->infoData(array('fields'=>'u_id,uef_overage','key'=>'u_id','val' => $u_id,'pager'=>false));
        if(!empty($uef_overage['uef_overage'])){
            if(intval($uef_overage['uef_overage']) >= $uwl_amount){
                /*处理提现过程start*/

                /*用户余额修改*/
                $funds_res = $this ->userFunds($u_id, $uwl_amount*-1, $type = 'withdraw');
                if(!$funds_res){
                    $this->exportData( array('msg'=>'提现申请失败'),0);
                }
                /*用户提现申请日志*/
                $WD_log_id = $this ->usersWithdrawLog($u_id, $uwl_amount, $uwl_truename, $uwl_card, $status = 0, $p_id);
                /*平台资金流向修改*/
                $dao_platform_funds_log = new \WDAO\Users(array('table'=>'platform_funds_log'));
                $data = array();
                $data['pfl_type'] = 1;
                $data['pfl_type_id'] = $WD_log_id;
                $data['pfl_amount'] = $uwl_amount;
                $data['pfl_in_time'] = time();
                $data['pfl_reason'] = 'withdraw';
                $data['pfl_status'] = 1;
                $dao_platform_funds_log -> addData($data);

                /*处理提现过程end*/
            }else{
                $this->exportData( array('msg'=>'用户余额不足'),0);
            }
        }else{
            $this->exportData( array('msg'=>'用户余额不足'),0);
        }


    }










































}

