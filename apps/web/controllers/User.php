<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-16 13:37:26
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-16 17:05:50
 */
namespace App\Controller;

class User extends \CLASSES\WebBase
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
        // $dao_verify_code = new \MDAO\Advertising(array('table'=>'advertising'));
        // $self_data = $dao_verify_code->infoData(array(
        //     'a_id' => $a_id,
        //     'pager'=>false,
        //     'fields'=>'a_id,a_title,a_info,a_link,a_type,a_status,a_start_time,a_end_time,r_id,a_img',
        //         ));
        // if($verify_code == $code)
        // {
        //     // 验证验证的时间
        //     /*需要短信发送的log表*/
        // }else{
        //     json_msg(0,'验证码不正确或验证码已过有效期');
        // }

        /*获取用户信息*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $user_data = $dao_users->listData(array(
            // 'key' => 'u_mobile', 'val' =>  $phone_number,
            'u_mobile' => $phone_number,
            'pager' => false,
            'fields'=>'u_id,u_name,u_pass',
                ));
        $token = $this->createToken($user_data['data']['0']['u_name'],$user_data['data']['0']['u_pass']);

        if($user_data['data']['0']['u_id'] > 0){
            /*用户存在*/

        }else{
            /*用户不存在*/
        }



    }

    /*发送短信验证码*/
    public function sendVerifyCode()
    {
        $phone_number = !empty($_GET['phone_number']) ? intavl($_GET['phone_number']) : '';
        if(!empty($phone_number))
        {
            $code = mt_rand(99999,999999);
            /*发送验证码接口*/
            /*最好是存库*/;
            $_SESSION['verify_code'] = $code;
        }
    }

    public function createToken($u_name,$u_pass)
    {
        return encyptPassword($u_name.$u_pass).'|'.base64_encode(time());
    }
}