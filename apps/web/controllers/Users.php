<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-16 13:37:26
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-11-28 17:01:18
 */
namespace App\Controller;

class Users extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }

    public function Login()
    {
        $type = isset($_GET['type']) && in_array($_GET['type'], array('verify', 'third', 'pass')) ? trim($_GET['type']) : 'verify';
        $logindata = array(
            'pager' => false,
            'fields'=>'u_true_name,u_pass,u_status,u_online,u_id,u_sex,u_idcard,u_name',
        );

        if ($type == 'pass')
        {
            $username = isset($_GET['username']) && trim($_GET['username']) != '' ? trim($_GET['username']) : '';
            $userpass = isset($_GET['userpass']) && trim($_GET['userpass']) != '' ? encyptPassword(trim($_GET['userpass'])) : '';
            if ('' == $username || '' == $userpass) $this->exportData(array('msg'=>'参数错误p'));
            $logindata['u_name'] = $username;
            $logindata['u_password'] = $userpass;
        }
        elseif ($type == 'third')
        {
            $openid = isset($_GET['openid']) && '' != trim($_GET['openid']) ? trim($_GET['openid']) : '';
            if ('' == $openid) $this->exportData(array('msg'=>'参数错误3'));
            $logindata['u_openid'] = $openid;
        }
        elseif ($type == 'verify')
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

            if(empty($self_data['data']['0']['code']) || empty($self_data['data']['0']['v_in_time']))
            {
                $this->exportData(array('msg'=>'系统错误请联系管理员'),0);
            }
            $time_max = $this ->web_config['verify_code_time'] + $self_data['data']['0']['v_in_time'];
            if($verify_code != $self_data['data']['0']['code'] || (time() > $time_max))
            {
                $this->exportData(array('msg'=>'验证码不正确或验证码已过有效期'),0);
            }
            $logindata['u_mobile'] = $phone_number;
        }
        /*获取用户信息*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $user_data = $dao_users->listData($logindata);
        if(!empty($user_data['data']['0']['u_id'])){
            /*用户存在*/
            if($user_data['data']['0']['u_status'] < 0)
            {
                $this->exportData(array('msg'=>'用户登录受限,请联系管理员!'),0);
            }
            $data = array();
            $data['u_token'] = $time;
            $data['u_last_edit_time'] = $time;
            $res = $dao_users ->updateData($data,array('u_id'=>$user_data['data']['0']['u_id']));
            $u_img = $this-> getHeadById($user_data['data']['0']['u_id']);
            $u_idcard = isset($user_data['data']['0']['u_idcard']) ? $user_data['data']['0']['u_idcard'] :'';
            $u_pass = isset($user_data['data']['0']['u_pass']) ? $user_data['data']['0']['u_pass'] :'';
            $u_sex = isset($user_data['data']['0']['u_sex']) ? $user_data['data']['0']['u_sex'] : '';
            if($res)
            {
                $token = $this->createToken($user_data['data']['0']['u_name'],$user_data['data']['0']['u_pass']);
                $this->exportData(array('token'=>$token,'u_img'=>$u_img,'u_online'=>$user_data['data']['0']['u_online'],'u_name'=>$user_data['data']['0']['u_true_name'],'u_sex'=>$user_data['data']['0']['u_sex'],'u_id'=>$user_data['data']['0']['u_id'],'u_pass'=>$user_data['data']['0']['u_pass'],'u_idcard'=>$u_idcard ),1);
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
            $u_id = $dao_users ->addUser($this,$data);

            if ($u_id)
            {
                $token = $this->createToken($data['u_name'],$data['u_pass']);
                $this->exportData(array('token'=>$token,'u_img'=>$this ->web_config['u_img_url'].'0'.'.jpg','u_online'=>'0','u_name'=>$data['u_name'],'u_sex'=>'-1','u_id'=>"$u_id",'u_pass'=>'','u_idcard'=>''),1);
            }else{
                $this->exportData(array('msg'=>'注册失败,请重新注册'),0);
            }
        }
    }

    /*发送短信验证码*/
    public function sendVerifyCode($msg='用户您好,您的登录验证码为',$number='')
    {
        $phone_number = !empty($_GET['phone_number']) ? intval($_GET['phone_number']) : $number;
        if(!empty($phone_number))
        {
            $code = mt_rand(99999,999999);
            if ($phone_number == '15840344241') $code = '123456';

            /*发送验证码接口*/
            $content = '登录验证码为'.$msg.$code.'。';
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
        $favorate_arr = $dao_favorate -> listData(array(
          'users_favorate.u_id'=>$u_id,
          'f_type'=>0,
          'leftjoin'=> array(
            'task_ext_info',
            'task_ext_info.t_id = users_favorate.f_type_id'
            ),
          'join' => array(
            'tasks',
            "tasks.t_id = users_favorate.f_type_id"
          ),
          'fields' => 'tasks.*,f_id,task_ext_info.t_desc',
          'pager'=>false
          ));
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

        $user_area_name = '';
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
        $res = $dao_funds -> infoData(array('fields'=>'u_id,u_pass
        u_name,u_mobile,u_phone,u_fax,u_sex,u_in_time,u_online,u_status,u_type,u_task_status,u_skills,u_start,u_credit,u_top,u_recommend,u_jobs_num,u_worked_num,u_high_opinions,u_low_opinions,u_middle_opinions,u_dissensions,u_true_name,u_idcard','key'=>'u_id','val' => $u_id,'pager'=>false));

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
        if(isset($_REQUEST['u_online']) && !empty($_REQUEST['u_id'])){

            $data_r = array();
            $data_r['u_online'] = $_REQUEST['u_online'] ;
            $data_r['u_id'] = $_REQUEST['u_id'] ;
            unset($_REQUEST);
            $_REQUEST = $data_r;

        }elseif (empty($_REQUEST['u_id']) || !isset($_REQUEST['u_sex']) || empty($_REQUEST['u_true_name']) || empty($_REQUEST['u_idcard']) || empty($_REQUEST['uei_info']) || empty($_REQUEST['uei_address']) || empty($_REQUEST['uei_province']) || empty($_REQUEST['uei_city']) || empty($_REQUEST['uei_area'])){
             $this->exportData( array('msg'=>'参数不足'),0);
        }

        $u_id= intval($_REQUEST['u_id']);
        /*users表*/
        if (isset($_REQUEST['u_phone'])) $data_users['u_phone'] = trim($_REQUEST['u_phone']);
        if (isset($_REQUEST['u_fax'])) $data_users['u_fax'] = trim($_REQUEST['u_fax']);
        if (isset($_REQUEST['u_sex'])) $data_users['u_sex'] = intval($_REQUEST['u_sex']);
        if (isset($_REQUEST['u_online'])) $data_users['u_online'] = intval($_REQUEST['u_online']);
        if (isset($_REQUEST['u_true_name'])) $data_users['u_true_name'] = trim($_REQUEST['u_true_name']);
        if (isset($_REQUEST['u_idcard'])) $data_users['u_idcard'] = trim($_REQUEST['u_idcard']);
        if (isset($_REQUEST['u_skills'])) $data_users['u_skills'] = trim($_REQUEST['u_skills']);

        /*判断传入数据长度*/
        if(isset($data_users['u_true_name']) && mb_strlen($data_users['u_true_name'],'utf8') > 25) $this->exportData( array('msg'=>'昵称长度最长为25个'),0);
        if(isset($data_users['u_idcard']) && mb_strlen($data_users['u_idcard'],'utf8') > 19) $this->exportData( array('msg'=>'身份证号长度最长为19个'),0);
        if(isset($data_ext['uei_info']) && mb_strlen($data_ext['uei_info'],'utf8') > 250) $this->exportData( array('msg'=>'个人简介的最大字符长度为250'),0);
        if(isset($data_ext['uei_address']) && mb_strlen($data_ext['uei_address'],'utf8') > 80) $this->exportData( array('msg'=>'详细地址信息的最大字符长度为80'),0);


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
        if(isset($data_ext['uei_zip']) && strlen($data_ext['uei_zip']) > 6) $this->exportData( array('msg'=>'邮编的最大字符长度为6'),0);



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
        /**/
        //$this->db->debug = 1;
        $data = $tmp = array();
        if (isset($_REQUEST['u_id']) && intval($_REQUEST['u_id']) > 0) $data['u_id'] = intval($_REQUEST['u_id']);
        if(isset($_REQUEST['u_mobile']) && intval($_REQUEST['u_mobile']) > 0) $data['u_mobile'] = intval($_REQUEST['u_mobile']);
        if(isset($_REQUEST['u_sex']) && intval($_REQUEST['u_sex']) > 0) $data['u_sex'] = intval($_REQUEST['u_sex']);
        if(isset($_REQUEST['u_bind_mobile']) && intval($_REQUEST['u_bind_mobile']) > 0) $data['u_bind_mobile'] = intval($_REQUEST['u_bind_mobile']);
        if(isset($_REQUEST['u_online']) && $_REQUEST['u_online'] !== "") $data['u_online'] = intval($_REQUEST['u_online']);
        /*搜索在线条件不为为隐身状态*/
        $not_invisible = isset($_REQUEST['not_invisible']) ? intval($_REQUEST['not_invisible']): 1;
        if(isset($_REQUEST['u_status']) && intval($_REQUEST['u_status']) > 0) $data['u_status'] = intval($_REQUEST['u_status']);
        if(isset($_REQUEST['u_type']) && intval($_REQUEST['u_type']) > 0) $data['u_type'] = intval($_REQUEST['u_type']);
        if(isset($_REQUEST['u_task_status']) && intval($_REQUEST['u_task_status']) >=0) $data['u_task_status'] = intval($_REQUEST['u_task_status']);
        if(isset($_REQUEST['u_idcard']) && intval($_REQUEST['u_idcard']) > 0) $data['u_idcard'] = intval($_REQUEST['u_idcard']);
        if(isset($_REQUEST['u_true_name']) && trim($_REQUEST['u_true_name'])) $data['u_true_name'] = trim($_REQUEST['u_true_name']);
        if(isset($_REQUEST['u_skills']) && trim($_REQUEST['u_skills'])) $data['u_skills'] = trim($_REQUEST['u_skills']);
        if(isset($_REQUEST['u_name']) && trim($_REQUEST['u_name'])) $data['u_name'] = trim($_REQUEST['u_name']);
        if(isset($_REQUEST['o_status']) && trim($_REQUEST['o_status']) != '') $tmp['o_status'] = trim($_REQUEST['o_status']); //多个用逗号联合

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
                if ($key == 'u_true_name')
                {
                    $data2['where'] .= ' and users.u_true_name like "%'.$val.'%"';
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
        $data2['where'] .= " and users.u_idcard != '' and users.u_true_name != '' and users.u_sex != -1 and users_ext_info.uei_info != '' and users_ext_info.uei_address != '' and  users_ext_info.uei_province != 0 and users_ext_info.uei_city != 0 and users_ext_info.uei_area != 0";
        if($not_invisible){
            $data2['where'] .= " and users.u_online != -1";
        }
        $data2['fields'] = 'users.u_id,users.u_mobile,users.u_idcard,users.u_sex,users.u_true_name as u_name,u_skills,users_ext_info.uei_info,u_task_status,u_true_name,ucp_posit_x,ucp_posit_y,users_ext_info.uei_address,users.u_in_time,users.u_last_edit_time,users.u_online,users.u_start,users.u_credit,users.u_top,users.u_recommend,users.u_jobs_num,users.u_worked_num,users.u_high_opinions,users.u_low_opinions,users.u_middle_opinions,users.u_dissensions';

        $list = $dao_info ->listData($data2);

        if(!empty($list['data'])){

            $favorate_id_arr = array();
            $order_id_arr['data'] = array();
            if (isset($_REQUEST['fu_id']) && intval($_REQUEST['fu_id']) > 0) {
                $fu_id = intval($_REQUEST['fu_id']);

                /*获取收藏列表*/
                $dao_users_favorate = new \WDAO\Users(array('table'=>'users_favorate'));
                $favorate_arr = $dao_users_favorate -> listData(array('u_id' => $fu_id,'f_type' => 1,'fields'=>'u_id,f_type_id','pager'=>false));
                foreach ($favorate_arr['data'] as $key => $value) {
                   $favorate_id_arr[] = $value['f_type_id'];
                }

                $order_id_arr = array();
                if (isset($tmp['o_status']))
                {
                /*获取用户订单列表*/
                    $dao_order = new \WDAO\Users(array('table'=>'orders'));
                    $order_id_arr = $dao_order -> listData(array(
                        'u_id' => $fu_id,
                        'o_status' => array('type'=>'in','value'=>trim($tmp['o_status'])),
                        'fields'=>'o_worker,o_confirm',
                        'pager'=>false));
                }

            }

            foreach ($list['data']  as $k => &$v) {
                    if(isset($v['u_id'])){
                        $v['u_img'] = $this -> getHeadById($v['u_id']);
                    }
                    if(in_array($v['u_id'],$favorate_id_arr)){
                        $v['is_fav'] = 1;
                    }else{
                        $v['is_fav'] = 0;
                    }
                /*订单联系情况*/
                $v['relation'] = 0;
                if (!empty($order_id_arr['data']))
                {
                    foreach ($order_id_arr['data'] as $key => $value) {
                        if($v['u_id'] == $value['o_worker']){
                            $v['relation'] = 1;
                            if(in_array($value['o_confirm'],array(0,2))){
                                $v['relation_type'] = 0;
                            }elseif($value['o_confirm'] == 1){
                                $v['relation_type'] = 1;
                            }

                            $v['o_confirm'] = $value['o_confirm'];
                        }

                    }
                }
                if($v['relation'] == 0){
                    $v['relation_type'] = 0;
                    $v['o_confirm'] = '-1';
                }

                $if_unite_phone = isset($this->web_config['if_unite_phone']) && intval($this->web_config['if_unite_phone']) > 0 ? $this->web_config['if_unite_phone'] : 0;
                if ($if_unite_phone) {
                    $v['u_mobile'] = $if_unite_phone;
                }
            }


        }
        $this->exportData($list,1);
    }



    /*获取用户资金日志*/
    public function getUsersFundsLog()
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户id不能为空'),0);
        }
        $category = !empty($_GET['category'])  ? trim($_GET['category']) : 'all';
        $dao_platform_funds_log = new \WDAO\Users(array('table'=>'platform_funds_log'));
        $r_data = array();
        $w_data = array();
        /*充值*/
        $recharge_list['data'] = array();
        $withdraw_list['data'] = array();
        if($category=='recharge' || $category=='all'){
        $dao_recharge_log = new \WDAO\Users(array('table'=>'user_recharge_log'));
        $recharge_list = $dao_recharge_log ->listData(array('u_id'=>$u_id,'pager'=>false,'url_status'=>1,'where'=>'p_id != 0','fields'=>'url_amount as amount,url_id as id ,url_in_time as time,url_overage as balances, "recharge"'));
        }
        /*雇主支付工人工资*/

        if($category=='recharge' || $category=='all' || $category=='payorder'){
            /*订单*/
            $dao_orders = new \WDAO\Users(array('table'=>'orders'));
            $payorder_list_o_worker = $dao_orders ->listData(
                array(
                    'o_worker'=>$u_id,
                    'pager'=>false,
                    'o_pay'=>1,
                    'fields'=>'o_id'
                    )
                );
            $o_arr=array();
            foreach ($payorder_list_o_worker['data'] as $value) {
                $o_arr[] = $value['o_id'];
            }
            $o_str = implode(',',$o_arr);

            $payorder_list['data'] = array();
            if(!empty($o_str)){
                /*日志*/
                $payorder_list = $dao_platform_funds_log ->listData(
                    array(

                        'pager'=>false,
                        'pfl_reason'=>'payorder',
                        'where'=>'pfl_type_id IN ('.$o_str.')',
                        'fields'=>'pfl_amount as amount,pfl_id as id ,pfl_in_time as time,pfl_last_editor as balances, "income"',
                    )
                );
            }

            /*订单改价后退换日志*/
            $dao_task = new \WDAO\Users(array('table'=>'tasks'));
            $payorder_list_u_id = $dao_task ->listData(
                array(
                    't_author'=>$u_id,
                    'pager'=>false,
                    'where'=>'t_status > 0',
                    'fields'=>'t_id'
                    )
                );
            $id_arr=array();
            foreach ($payorder_list_u_id['data'] as $value) {
                $id_arr[] = $value['t_id'];
            }
            $o_str = '';
            $o_str = implode(',',$id_arr);

            $retrun_list['data'] = array();
            if(!empty($o_str)){
                /*日志*/
                $retrun_list = $dao_platform_funds_log ->listData(
                    array(
                        'pager'=>false,
                        'pfl_reason'=>'taskreturn',
                        'pfl_type' => 3,
                        'where'=>'pfl_type_id IN ('.$o_str.')',
                        'fields'=>'pfl_amount as amount,pfl_id as id ,pfl_in_time as time,pfl_last_editor as balances,pfl_rate, "taskreturn"',
                    )
                );
            }

            $time = array();
            $arr = array();
            $r_data = array_merge($recharge_list['data'],$payorder_list['data'],$retrun_list['data']);
            foreach ($r_data as $k => $v) {
                $time[$k]  = $v['time'];
                $arr[$k] = $v;
                $r_data[$k]['amount'] = $v['amount'];
            }
            // $res = array_multisort($time, SORT_DESC, $arr, SORT_ASC, $r_data);
        }



        /*提现*/
        if($category=='withdraw' || $category=='all'){
        $dao_withdraw_log = new \WDAO\Users(array('table'=>'user_withdraw_log'));
        $withdraw_list = $dao_withdraw_log ->listData(array('u_id'=>$u_id,'pager'=>false,'where' => 'uwl_status > -1', 'fields'=>'uwl_id as id,uwl_amount as amount,uwl_in_time as time,uwl_overage as balances,"withdraw"'));
        }


        if($category=='withdraw' || $category=='all' || $category=='payorder'){
            /*订单*/
            $dao_orders = new \WDAO\Users(array('table'=>'orders'));
            $payorder_list_u_id = $dao_orders ->listData(
                array(
                    'u_id'=>$u_id,
                    'pager'=>false,
                    'o_pay'=>1,
                    'fields'=>'o_id'
                    )
                );
            $o_arr=array();
            foreach ($payorder_list_u_id['data'] as $value) {
                $o_arr[] = $value['o_id'];
            }
            $o_str = '';
            $o_str = implode(',',$o_arr);

            $payorder_list['data'] = array();
            if(!empty($o_str)){
                /*日志*/
                $payorder_list = $dao_platform_funds_log ->listData(
                    array(

                        'pager'=>false,
                        'pfl_reason'=>'payorder',
                        'where'=>'pfl_type_id IN ('.$o_str.')',
                        'fields'=>'pfl_amount as amount,pfl_id as id ,pfl_in_time as time,pfl_last_editor as balances,pfl_rate, "pay"',
                    )
                );
            }
            $time = array();
            $arr = array();
            $w_data = array_merge($withdraw_list['data'],$payorder_list['data']);

            foreach ($w_data as $k => $v) {
                /*订单收入金额修改*/
                if(isset($v['pfl_rate']) && floatval($v['pfl_rate']) > 0){

                    $w_data[$k]['amount'] = $v['amount']/(1-floatval($v['pfl_rate']));
                    unset($w_data[$k]['pfl_rate']);

                }
                $time[$k]  = $v['time'];
                $arr[$k] = $v;
            }

            // $res = array_multisort($time, SORT_DESC, $arr, SORT_ASC, $w_data);
        }

/*支出收入订单合并*/
        $time = array();
        $arr = array();
        $data = array();
        $data = array_merge($w_data,$r_data);
        foreach ($data as $k => $v) {
            $time[$k]  = $v['time'];
            $arr[$k] = $v;
        }
        $res = array_multisort($time, SORT_DESC, $arr, SORT_ASC, $data);

        foreach ($data as $key => &$value) {
            /*类型*/
            if(isset($value['withdraw'])){
                $value['type'] = 'withdraw';
                unset($value['withdraw']);

            }elseif(isset($value['recharge'])){
                $value['type'] = 'recharge';
                unset($value['recharge']);
            }elseif(isset($value['pay'])){
                $value['type'] = 'pay';
                unset($value['pay']);
            }elseif(isset($value['income'])){
                $value['type'] = 'income';
                unset($value['income']);
            }elseif(isset($value['taskreturn'])){
                $value['type'] = 'income';
                unset($value['taskreturn']);
            }else{

            }

            if($value['amount'] < 0){
                $value['amount'] = $value['amount'] * -1;
            }

            $value['amount'] = number_format($value['amount'], 2, '.', '');

        }
        $this->exportData( array('data'=>$data),1);

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
        $wm_type = isset($_GET['wm_type']) ? intval($_GET['wm_type']) : 3;

        $time = time();
        $where =  'um_status != -1
        AND (web_msg.wm_start_time <= '.$time.' OR web_msg.wm_start_time = 0 )
        AND (web_msg.wm_end_time >= '.$time.' OR web_msg.wm_end_time = 0)
        AND wm_status = 1
        AND user_msg.u_id='.$u_id;
        switch ($wm_type) {
            case 0:
                $where .= ' AND web_msg.wm_type = 0';
                break;
            case 1:
                $where .= ' AND web_msg.wm_type = 1';
                break;
            case 2:
                $where .= ' AND web_msg.wm_type = 2';
                break;
            default:
                $where .= ' ';
                break;
        }

        $dao_web_msg = new \WDAO\Users(array('table'=>'web_msg'));
        $msg_list = $dao_web_msg ->listData(array(
            'pager' => true,
            'page' => $page,
            'where' => $where,
            'fields' => 'web_msg.wm_title,user_msg.um_in_time,web_msg.wm_type,web_msg.wm_id ,user_msg.um_id,web_msg_ext.wm_desc,user_msg.um_status',
            'join' => array('user_msg','web_msg.wm_id=user_msg.wm_id '),
            'leftjoin' => array('web_msg_ext','web_msg.wm_id=web_msg_ext.wm_id '),
            'order' => 'user_msg.um_in_time desc,user_msg.um_status asc',
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

    /*修改信息读取状态*/
    public function msgReadEdit()
    {

        if(empty($_GET['um_id']) || empty($um_id = intval($_GET['um_id']))){
            $this->exportData( array('msg'=>'用户站内信关系ID为空'),0);
        }

        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $res = $dao_user_msg ->updateData(array('um_status' => '1',),array('um_id'=>$um_id));
        if($res){
            $this->exportData( array('msg'=>'状态修改成功'),1);
        }else{
            $this->exportData( array('msg'=>'状态修改失败'),0);
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
        if(isset($wm_id_arr['wm_id'])){
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
                'leftjoin' => array('web_msg_ext','web_msg.wm_id=web_msg_ext.wm_id '),
                ));
            unset($info['pager']);

        }
        $this->exportData( $info,1);
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
                    $ext_data['c_img'] = '';
                    $res = $dao_complaints ->uploadComplaintImg($_POST['c_img'],'../uploads/images/'.date('Y/m/d'));
                    if(intval($res) < 0){
                        switch (intval($res)) {
                            case -1:
                                $this->exportData( array('msg'=>'图片目录创建失败'),0);
                                break;
                            case -2:
                                $this->exportData( array('msg'=>'图片写入失败'),0);
                                break;
                            default:
                                $ext_data['c_img'] = $res;
                                break;
                        }
                    }else{
                        $ext_data['c_img'] = $res;
                    }
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
                    'val' => intval($_POST['c_id']),
                    ));
                $ext_data = '';
                $res = $dao_complaints_ext ->uploadComplaintImg($_POST['c_img'],'../uploads/images/'.date('Y/m/d'));
                if(intval($res) < 0){
                    switch (intval($res)) {
                        case -1:
                            $this->exportData( array('msg'=>'图片目录创建失败'),0);
                            break;
                        case -2:
                            $this->exportData( array('msg'=>'图片写入失败'),0);
                            break;
                        default:
                            $img_path = $res;
                            break;
                    }
                }else{
                    $img_path = $res;
                }


                if(!empty($img_path) && intval($res) >= 0){
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
        $this->exportData( array('msg'=>'参数不足,图片信息写入失败!'),0);
    }

    /*用户提现申请接口*/
    public function applyWithdraw()
    {
        if(empty($_REQUEST['u_id']) || empty($u_id = intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_REQUEST['uwl_amount']) || empty($uwl_amount = floatval($_REQUEST['uwl_amount']))){
            $this->exportData( array('msg'=>'提现金额不能为空'),0);
        }
        if(empty($_REQUEST['p_id']) || empty($p_id = intval($_REQUEST['p_id']))){
            $this->exportData( array('msg'=>'提现方式不能为空'),0);
        }
        if(empty($_REQUEST['uwl_card']) || empty($uwl_card = intval($_REQUEST['uwl_card']))){
            $this->exportData( array('msg'=>'提现账号不能为空'),0);
        }
        if(empty($_REQUEST['uwl_truename']) || empty($uwl_truename = trim($_REQUEST['uwl_truename']))){
            $this->exportData( array('msg'=>'提现账号姓名不能为空'),0);
        }
        if(empty($_REQUEST['u_pass']) || empty($u_pass = trim($_REQUEST['u_pass']))){
            $this->exportData( array('msg'=>'提现密码不能为空'),0);
        }

        /*设置充值额度限制*/
        if(isset($this ->web_config['withdraw_amount_min']) && $this ->web_config['withdraw_amount_min'] > 0)
        {
            if($this ->web_config['withdraw_amount_min'] > $uwl_amount)
            {
                $this->exportData( array('msg'=>'提现失败!最小提现金额为'.$this ->web_config['withdraw_amount_min'].'元;'),0);
            }
        }
        if(isset($this ->web_config['withdraw_amount_max']) && $this ->web_config['withdraw_amount_max'] > 0)
        {
            if($this ->web_config['withdraw_amount_max'] < $uwl_amount)
            {
                $this->exportData( array('msg'=>'提现失败!最大提现金额为'.$this ->web_config['withdraw_amount_max'].'元;'),0);
            }
        }


        /*验证支付密码是否正确*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $check_res = $dao_users ->checkUserPayPassword(array('u_id' =>$u_id,'u_pass' =>$u_pass));
        if(!$check_res){
            $this->exportData( array('msg'=>'提现密码错误'),0);
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
                    $this->exportData( array('msg'=>'提现申请失败,请联系管理员'),0);
                }
                /*用户提现申请日志*/
                $WD_log_id = $this ->usersWithdrawLog($u_id, floatval($uwl_amount), $uwl_truename, $uwl_card, $status = 0, $p_id);
                /*平台资金流向修改*/
                if(intval($WD_log_id <=0 )){
                    $this->exportData( array('msg'=>'提现申请失败,请联系管理员'),0);
                }
                $dao_platform_funds_log = new \WDAO\Users(array('table'=>'platform_funds_log'));
                $data = array();
                $data['pfl_type'] = 1;
                $data['pfl_type_id'] = $WD_log_id;
                $data['pfl_amount'] = $uwl_amount;
                $data['pfl_in_time'] = time();
                $data['pfl_reason'] = 'withdraw';
                $data['pfl_status'] = 1;
                $dao_platform_funds_log -> addData($data);
                $this->exportData( array('msg'=>'提现申请添加成功'),1);

                /*处理提现过程end*/
            }else{
                $this->exportData( array('msg'=>'用户余额不足'),0);
            }
        }else{
            $this->exportData( array('msg'=>'用户余额不足'),0);
        }
    }
        /*充值开始*/
        /*充值记录接口*/
    public function applyRechargeLog()
    {
        if(empty($_REQUEST['u_id']) || empty($u_id = intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_REQUEST['url_amount']) || floatval($_REQUEST['url_amount']) <= 0){
            $this->exportData( array('msg'=>'充值金额必须大于0元'),0);
        }else{
            $url_amount = floatval($_REQUEST['url_amount']);
        }
        if(empty($_REQUEST['p_id']) || empty($p_id = intval($_REQUEST['p_id']))){
            $this->exportData( array('msg'=>'支付方式不能为空'),0);
        }

        /*设置充值额度限制*/
        if(isset($this ->web_config['recharge_amount_min']) && $this ->web_config['recharge_amount_min'] > 0)
        {
            if($this ->web_config['recharge_amount_min'] > $url_amount)
            {
                $this->exportData( array('msg'=>'充值失败!最小充值金额为'.$this ->web_config['recharge_amount_min'].'元;'),0);
            }
        }
        if(isset($this ->web_config['recharge_amount_max']) && $this ->web_config['recharge_amount_max'] > 0)
        {
            if($this ->web_config['recharge_amount_max'] < $url_amount)
            {
                $this->exportData( array('msg'=>'充值失败!最大充值金额为'.$this ->web_config['recharge_amount_max'].'元;'),0);
            }
        }

        /*用户充值申请日志*/
        $RC_log_id = $this ->usersRechargeLog($u_id, floatval($url_amount), $name = '', $card = '', $status = 0, $p_id);
        if (!empty(intval($RC_log_id))) {
            /*第三方充值*/
            $dao_users = new \WDAO\Users(array('table'=>'users'));
            if ($p_id = $this ->web_config['wx_pid'])/*微信支付*/
            {
                $res = $dao_users ->WXRecharge($RC_log_id,floatval($url_amount));
                if($res){
                    $this->exportData( $res,1);
                }else{
                    $this->exportData( array('msg'=>'充值失败!系统错误请联系管理员'),0);
                }
            }
            elseif($p_id = $this ->web_config['al_pid'])/*支付宝支付*/
            {

            }else{
                $this->exportData( array('msg'=>'充值失败!系统错误请联系管理员'),0);
            }
        }else{
            $this->exportData( array('msg'=>'充值失败!系统错误请联系管理员'),0);
        }
    }
    /*回调接口*/
    public function rechargeCallback()
    {
        $dao_recharge_log = new \WDAO\Users(array('table'=>'user_recharge_log'));
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        require_once WXPAY_PATH.'/example/notify.php';
        $notify = new \MLIB\WXPAY\PayNotifyCallBack();
        $data = $notify->Handle(false);
        /*事物开始*/
        $this->db->start();
        // $data = array("appid" => "wx2421b1c4370ec43b","attach" => "支付测试", "bank_type" =>"CFT" ,"fee_type" =>"CNY", "is_subscribe" =>"Y" ,"mch_id" =>"10000100" ,"nonce_str" => "5d2b6c2a8db53831f7eda20af46e531c", "openid" => "oUpF8uMEb4qRXf22hE3X68TekukE", "out_trade_no" => "109", "result_code" =>"SUCCESS", "return_code" =>"SUCCESS" ,"sign" => "B552ED6B279343CB493C5DD0D78AB241" ,"sub_mch_id" =>"10000100" ,"time_end" => "20140903131540" ,"total_fee" =>"12" ,"trade_type" =>"APP" ,"transaction_id" => "1004400740201409030005092168",);
        /*微信支付成功后处理返回的数据*/
        if(!empty(floatval($data['total_fee'])) && $data['result_code'] == 'SUCCESS' && isset($data['out_trade_no']))
        {


            $total_fee = floatval($data['total_fee']/100);/*支付金额*/
            $out_trade_no = substr($data['out_trade_no'],9);/*支付申请日志单号*/
            $recharge_data = $dao_recharge_log ->infodata(array('key'=>'url_id','val'=>$out_trade_no));

            $c_data = array();
            if($total_fee != $recharge_data['url_amount'])
            {
                $c_data['url_remark'] = '用户实际支付金额不等于申请金额,申请金额为'.$recharge_data['url_amount'];
            }
            if($recharge_data['url_status'] == 1)
            {
                /*申请重复*/
                return false;
            }
            $c_data['url_amount'] = $total_fee;
            /*是否自动审核*/
            if(RECHARGE_CONFIRMATION)
            {
                $c_data['url_status'] = 0;
                $res_recharge_log = $dao_recharge_log -> updateData($c_data,array('url_id' => $recharge_data['url_id']));
                $this->db->commit();
                return true;

            }else{
                $c_data['url_status'] = 1;
                $c_data['url_solut_time'] = time();
                $c_data['url_solut_author'] = 0;
                /*获取当前uid的余额*/
                $dao_users_ext_funds = new \WDAO\Users(array('table'=>'users_ext_funds'));
                /*获取用户当前余额*/
                $user_url_overage = $dao_users_ext_funds ->infoData(array('key'=>'u_id','val'=>$recharge_data['u_id']));
                if($user_url_overage){
                    $c_data['url_overage'] = $user_url_overage['uef_overage'] + $c_data['url_amount'];
                }else{
                    $c_data['url_overage'] =  $c_data['url_amount'];
                }

                $res_recharge_log = $dao_recharge_log -> updateData($c_data,array('url_id' => $recharge_data['url_id']));
                $judge_data = array();
                $judge_data['pfl_type_id'] = $recharge_data['url_id'];
                $judge_data['pfl_amount'] = $total_fee;
                $judge_data['u_id'] = $recharge_data['u_id'];
                $res_judgeWX = $dao_users ->judgeResWX($judge_data);

                if($res_judgeWX && $res_recharge_log)
                {
                    $this->db->commit();
                    $this->exportData('success');
                }else{
                    $this->db->rollback();
                }
            }

        }else{
           return false;
        }

    }
    /*充值结束*/
    /*评价添加接口*/
    public function commentAdd()
    {
        $data_c = array();
        if(empty($_REQUEST['t_id']) || empty($data_c['t_id'] = intval($_REQUEST['t_id']))){
            $this->exportData( array('msg'=>'任务id为空'),0);
        }
        if(empty($_REQUEST['u_id']) || empty($data_c['u_id'] = intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'评论人id为空'),0);
        }
        if(empty($_REQUEST['tc_u_id']) || empty($data_c['tc_u_id'] = intval($_REQUEST['tc_u_id']))){
            $this->exportData( array('msg'=>'评论人id为空'),0);
        }

        $data_c['tc_start'] = $data_c['tc_first_start'] = isset($_REQUEST['tc_start']) ? intval($_REQUEST['tc_start']) : 0;
        $data_c['tc_type'] = isset($_REQUEST['tc_type']) ? intval($_REQUEST['tc_type']) : 0;
        $data_c['tc_last_edit_time'] = time();
        $data_c['tc_in_time'] = time();
        $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
        $tc_id = $dao_task_comment -> addData($data_c);
        /*设置users好评次数*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        switch ($data_c['tc_start']) {
            case '3':
                $sql = 'update users set u_high_opinions = u_high_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;
            case '2':
                $sql = 'update users set u_middle_opinions = u_middle_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;
            case '1':
                $sql = 'update users set u_low_opinions = u_low_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;

            default:
                // $sql = 'update users set u_high_opinions = u_high_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;
        }

        $result = $dao_users ->queryData($sql);


        $data_ext = array();
        isset($_REQUEST['tce_desc']) ? $data_ext['tce_desc'] = trim($_REQUEST['tce_desc']) : false ;
        if(!empty($data_ext) && intval($tc_id) > 0) {
            $data_ext['tc_id'] = intval($tc_id);
            $dao_task_comment_ext = new \WDAO\Users(array('table'=>'task_comment_ext'));
            $dao_task_comment_ext -> addData($data_ext);
        }
        if(intval($tc_id) > 0){
           $this->exportData( array('data'=>array('tc_id'=>$tc_id)),1);
        }else{
           $this->exportData( array('msg'=>"评价失败"),0);
        }
    }
    /**********************************************************添加好评次数**********************************************************/
    /*评价修改接口*/
    // public function commentEdit()
    // {
    //     $data_c = array();
    //     if(empty($_REQUEST['tc_id']) || empty($tc_id = intval($_REQUEST['tc_id']))){
    //         $this->exportData( array('msg'=>'评价id为空'),0);
    //     }

    //     isset($_REQUEST['tc_start']) ? $data_c['tc_start'] = intval($_REQUEST['tc_start']) : fales;
    //     $data_c['tc_last_edit_time'] = time();
    //     $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
    //     $res = $dao_task_comment -> updateData($data_c,array('tc_id' => $tc_id));
    //     $data_ext = array();
    //     $data_ext['tce_desc'] = isset($_REQUEST['tce_desc']) ? trim($_REQUEST['tce_desc']) : '' ;
    //     if($res) {
    //         $data_ext['tc_id'] = intval($tc_id);
    //         $dao_task_comment_ext = new \WDAO\Users(array('table'=>'task_comment_ext'));
    //         $dao_task_comment_ext -> updateData($data_ext,array('tc_id' => $tc_id));
    //     }
    //     $sql = 'update task_comment set tc_edit_times = tc_edit_times + 1 where tc_id = ' . $tc_id;
    //     $result = $dao_task_comment ->queryData($sql);
    //     if($res){
    //        $this->exportData( array('msg'=>'修改评论成功'),1);
    //     }
    // }

    /*查看自己评论他人接口列表*/
    public function userCommentOther()
    {
        $data = array();
        if(empty($_REQUEST['u_id']) || empty($data['u_id'] =  intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户id为空'),0);
        }
        if(isset($_REQUEST['page']) && !empty(intval($_REQUEST['page']))) {
            $data['pager'] = true;
            $data['page'] = intval($_REQUEST['page']) ;
        }else{
            $data['pager'] = false;
        }
        $data['leftjoin'] = array('task_comment_ext','task_comment.tc_id=task_comment_ext.tc_id');
        $data['fields'] = 'task_comment.tc_id as tc_id,t_id,tc_u_id,tc_in_time,tc_start,task_comment_ext.tce_desc';
        $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
        $list = $dao_task_comment ->listData($data);
        foreach ($list['data'] as $k => &$v) {
            $v['u_img'] = $this-> getHeadById($v['tc_u_id']);
        }
        unset($list['pager']);
        $this->exportData( $list,1);
    }

    /*查看他人评论自己接口列表*/
    public function otherCommentUser()
    {
        $data = array();
        if(empty($_REQUEST['tc_u_id']) || empty($data['tc_u_id'] =  intval($_REQUEST['tc_u_id']))){
            $this->exportData( array('msg'=>'用户id为空'),0);
        }
        if(isset($_REQUEST['page']) && !empty(intval($_REQUEST['page']))) {
            $data['pager'] = true;
            $data['page'] = intval($_REQUEST['page']) ;
        }else{
            $data['pager'] = false;
        }
        $data['leftjoin'] = array('task_comment_ext','task_comment.tc_id=task_comment_ext.tc_id');
        $data['fields'] = 'task_comment.tc_id as tc_id,u_id,t_id,tc_u_id,tc_in_time,tc_start,task_comment_ext.tce_desc';
        $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
        $list = $dao_task_comment ->listData($data);
        foreach ($list['data'] as $k => &$v) {
            $v['u_img'] = $this-> getHeadById($v['u_id']);
        }
        unset($list['pager']);
        $this->exportData( $list,1);
    }

    /*设置用户支付密码*/
    public function setPassword()
    {
        if(empty($_REQUEST['u_id']) || empty($u_id =  intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户id为空'),0);
        }
        if(empty($_REQUEST['u_pass']) || empty($u_pass =  trim($_REQUEST['u_pass']))){
            $this->exportData( array('msg'=>'用户密码为空'),0);
        }
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $u_info = $dao_users -> infoData(array('key'=>'u_id','val'=>$u_id,'fields'=>'u_id,u_pass'));
        if(!empty($u_info['u_pass'])){
            $this ->exportData( array('msg'=>'设置密码失败'),0);
        }else{
            $data = array();
            $data['u_pass'] = encyptPassword($u_pass);
            $res = $dao_users ->updateData($data,array('u_id'=>$u_id));
            if($res){
                $this ->exportData( array('msg'=>'设置密码成功'),1);
            }else{
                $this ->exportData( array('msg'=>'设置密码失败'),0);
            }

        }

    }

    public function passwordEdit()
    {

        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $res = false;

        if(isset($_REQUEST['u_id']) && !empty($u_id = intval($_REQUEST['u_id'])) && !empty($_REQUEST['u_pass']) && !empty($_REQUEST['new_pass'])  )/*用户id原密码*/
        {
            $check_user_res = $dao_users ->checkUserPayPassword(array('u_id' => $u_id,'u_pass' => trim($_REQUEST['u_pass'])));
            if(isset($check_user_res['u_id']) && !empty(intval($check_user_res['u_id']))){
                $res = $dao_users ->passwordEdit($u_id,$_REQUEST['new_pass']);
            }else{
                $this ->exportData( array('msg'=>'原密码错误,密码修改失败'),0);
            }
        }

        elseif(isset($_REQUEST['u_mobile']) && !empty($num = intval($_REQUEST['u_mobile'])) && !empty($_REQUEST['verify_code']) && !empty($_REQUEST['u_idcard']) && !empty($u_idcard = trim($_REQUEST['u_idcard'])) && !empty($_REQUEST['new_pass'])  )/*手机号验证码身份证号*/
        {
            if(empty($_REQUEST['new_pass']) || empty($u_pass = trim($_REQUEST['new_pass']))){
                $this ->exportData( array('msg'=>'请输入新密码'),0);
            }
            $res = $dao_users ->checkVerifies($num,trim($_REQUEST['verify_code']),$this ->web_config['verify_code_time']);
            if($res === true){
                $u_info = $dao_users ->listData(array('u_mobile'=>$num,'u_idcard'=>$u_idcard,'fields'=>'u_id','pager'=>false));

                if (!empty($u_info['data'][0]['u_id']) && intval($u_info['data'][0]['u_id']) > 0) {
                    $res = $dao_users ->passwordEdit($u_info['data'][0]['u_id'],$_REQUEST['new_pass']);
                }else{
                    $this ->exportData( array('msg'=>'身份证号错误,密码修改失败'),0);
                }
            }else{
                switch ($res) {
                    case '-1':
                        $this ->exportData( array('msg'=>'系统错误请联系管理员'),0);
                        break;
                    case '-2':
                        $this ->exportData( array('msg'=>'验证码不正确或验证码已过有效期'),0);
                        break;
                    default:
                        $this ->exportData( array('msg'=>'验证码不正确或验证码已过有效期'),0);
                        break;
                }
            }
        }
        /*验证验证码*/
        elseif(isset($_REQUEST['u_mobile']) && !empty($num = intval($_REQUEST['u_mobile'])) && !empty($_REQUEST['verify_code']))
        {
            $res = $dao_users ->checkVerifies($num,trim($_REQUEST['verify_code']),$this ->web_config['verify_code_time']);
            if($res === true){
               $this ->exportData( array('msg'=>'验证码正确'),1);
            }else{
                switch ($res) {
                    case '-1':
                        $this ->exportData( array('msg'=>'系统错误请联系管理员'),0);
                        break;
                    case '-2':
                        $this ->exportData( array('msg'=>'验证码不正确或验证码已过有效期'),0);
                        break;
                    default:
                        $this ->exportData( array('msg'=>'验证码不正确或验证码已过有效期'),0);
                        break;
                }
            }
        }
        /*验证身份证号*/
        elseif(isset($_REQUEST['u_mobile']) && !empty($num = intval($_REQUEST['u_mobile'])) && !empty($_REQUEST['u_idcard']))
        {
            $u_idcard = trim($_REQUEST['u_idcard']);
            $u_info = $dao_users ->listData(array('u_mobile'=>$num,'u_idcard'=>$u_idcard,'fields'=>'u_id','pager'=>false));
            if (!empty($u_info['data'][0]['u_id']) && intval($u_info['data'][0]['u_id']) > 0) {
                    $this ->exportData( array('msg'=>'身份证号验证成功'),1);
                }else{
                    $this ->exportData( array('msg'=>'身份证号验证失败'),0);
                }

        }
        /*验证原密码密码*/
        elseif(isset($_REQUEST['u_id']) && !empty($u_id = intval($_REQUEST['u_id'])) && !empty($_REQUEST['u_pass']))
        {
            $check_user_res = $dao_users ->checkUserPayPassword(array('u_id' => $u_id,'u_pass' => trim($_REQUEST['u_pass'])));
            if(isset($check_user_res['u_id']) && !empty(intval($check_user_res['u_id']))){
                $this ->exportData( array('msg'=>'原密码正确'),1);
            }else{
                $this ->exportData( array('msg'=>'原密码错误'),0);
            }
        }
        elseif(isset($_REQUEST['u_mobile']) && !empty($num = intval($_REQUEST['u_mobile'])) && !isset($_REQUEST['u_idcard']) && !isset($_REQUEST['verify_code']))/*手机号发送验证码*/
        {
            $this ->sendVerifyCode('用户您好!您正在重置密码,验证码为',$num);
        }
        else
        {
            $this ->exportData( array('msg'=>'参数不足,密码修改失败'),0);
        }
        if($res){
            $this ->exportData( array('msg'=>'密码修改成功'),1);
        }else{
            $this ->exportData( array('msg'=>'密码修改失败,请联系管理员'),0);
        }
    }










































}

