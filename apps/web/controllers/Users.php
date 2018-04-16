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
            'fields'=>'u_true_name,u_pass,u_status,u_online,u_id,u_sex,u_idcard,u_name',
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
            $u_idcard = isset($user_data['data']['0']['u_idcard']) ? $user_data['data']['0']['u_idcard'] :'';
            $u_pass = isset($user_data['data']['0']['u_pass']) ? $user_data['data']['0']['u_pass'] :'';
            $u_sex = isset($user_data['data']['0']['u_sex']) ? $user_data['data']['0']['u_sex'] : '';
            if($res){
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
        include_once MANAGEPATH . '/regions.php';
        if (isset($ext_info['uei_province']) && $ext_info['uei_province'] > 0) $user_area_name .= $regions[$ext_info['uei_province']]['r_name'];
        $user_area_name .= ' ';
        if (isset($ext_info['uei_city']) && $ext_info['uei_city'] > 0) $user_area_name .= $regions[$ext_info['uei_city']]['r_name'];
        $user_area_name .= ' ';
        if (isset($ext_info['uei_area']) && $ext_info['uei_area'] > 0) $user_area_name .= $regions[$ext_info['uei_area']]['r_name'];
        
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
        if(isset($_REQUEST['u_online']) && !empty($_REQUEST['u_id'])) {

            $data_r = array();
            $data_r['u_online'] = $_REQUEST['u_online'];
            $data_r['u_id'] = $_REQUEST['u_id'];
            unset($_REQUEST);
            $_REQUEST = $data_r;
        }
        elseif (empty($_REQUEST['u_id'])  || empty($_REQUEST['u_true_name']) || empty($_REQUEST['uei_province']) || empty($_REQUEST['uei_city']) ){
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

