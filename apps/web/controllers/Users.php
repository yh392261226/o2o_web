<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-16 13:37:26
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-18 16:41:58
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
            json_msg(0,'手机号或验证码不能为空');
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
            json_msg(0,'系统错误请联系管理员');
        }
        $time_max = $this ->web_config['verify_code_time'] + $self_data['data']['0']['v_in_time'];
        $time = time();

        if($verify_code != $self_data['data']['0']['code'] || ($time > $time_max))
        {
            json_msg(0,'验证码不正确或验证码已过有效期');
        }

        /*获取用户信息*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $user_data = $dao_users->listData(array(
            'u_mobile' => $phone_number,
            'pager' => false,
            'fields'=>'u_id,u_name,u_pass,u_status',
                ));




        if(!empty($user_data['data']['0']['u_id'])){
            /*用户存在*/
            if($user_data['data']['0']['u_status'] < 0){
                json_msg(0,'用户登录受限,请联系管理员!');
            }
            $data = array();
            $data['u_token'] = $time;
            $data['u_last_edit_time'] = $time;
            $res = $dao_users ->updateData($data,array('u_id'=>$user_data['data']['0']['u_id']));
            if($res){
                $token = $this->createToken($user_data['data']['0']['u_name'],$user_data['data']['0']['u_pass']);
                json_msg(1,array('token'=>$token));
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
            $res = $dao_users ->addData($data);
            if($res){
                $token = $this->createToken($data['u_name'],$data['u_pass']);
                json_msg(1,array('token'=>$token));
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
            /*存库*/;
            $dao_verify_code = new \WDAO\Verifies(array('table'=>'verifies'));
            $data = array();
            $data['u_mobile'] = $phone_number;
            $data['code'] = $code;
            $data['v_in_time'] = time();
            $dao_verify_code->addData($data);
        }
    }

    private function createToken($u_name,$u_pass,$hash='')
    {

        if($hash=='') $hash = time();
        return encyptPassword($u_name.$u_pass).'|'.base64_encode($hash);
    }
}