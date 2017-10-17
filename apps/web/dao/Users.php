<?php
namespace WDAO;

class Users extends \MDAOBASE\DaoBase
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function uploadComplaintImg($content='',$path_dir='')
    {
        $filename = uniqid().'.jpg';/*临时文件名*/

        /*这里是转码 Unicode转Native*/
        $param2 = str_replace(" ","+",$content);
        $param2 = str_replace("%2F","/",$param2);
        $param2 = str_replace("%2B","+",$param2);
        $param2 = str_replace("%0A","",$param2);

        $content = base64_decode($param2); // 将格式为base64的字符串解码

        /*如果文件写入成功*/
        if(!is_dir($path_dir)){
            $res = mkdir($path_dir,0777,true);
            if(!$res){
                return -1;/*图片目录创建失败*/
            }
        }
        if(!empty($content)){
            if (file_put_contents($path_dir.$filename, $content))
            {
                $imageInfo = getimagesize ($path_dir.$filename);/*验证图片*/
                if ($imageInfo == false) {
                    unlink($path_dir.$filename);
                    $this->exportData( array('msg'=>'非法上传'),0);
                }
                \Swoole\Image::thumbnail($path_dir.$filename,
                            $path_dir.'/cp_'.$filename,
                            500,/*图片宽*/
                            500,/*图片高*/
                            1000);
                unlink($path_dir.$filename);
                return $path_dir.'/cp_'.$filename;

            }else{
                return -2;/*图片写入失败*/
            }
        }

    }

    /**
     * 验证支付密码
     * @param array $data
     * @return 失败返回false 成功返回含有uid及手机号的数组
     *
     */
    public function checkUserPayPassword($data = array())
    {
        if (!empty($data) && isset($data['u_id']) && 0 < intval($data['u_id']) && isset($data['u_pass']) && '' != trim($data['u_pass']))
        {
            $info = $this->infoData(intval($data['u_id']));
            if (!empty($info) && isset($info['u_pass']))
            {
                if ($info['u_pass'] != encyptPassword($data['u_pass']))
                {
                    return false;
                }
                return array('u_mobile' => $info['u_mobile'], 'u_id' => $info['u_id']);
            }
        }
        return false;
    }

    /**
     * [checkVerifies description]验证短信验证码
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-10-14
     * @param  [type]            $phone_number [description]手机号
     * @param  [type]            $verifies     [description]验证码
     * @param  [type]            $max_time     [description]有效时长
     * @return [bool]                          [description]
     */
    public function checkVerifies($phone_number,$verifies,$valid_time)
    {
         /*获取验证码信息*/
        $dao_verify_code = new \WDAO\Verifies(array('table'=>'verifies'));
        $self_data = $dao_verify_code->listData(array(
            'u_mobile' => $phone_number,
            'fields' => 'code,v_in_time',
            'limit' => 1,
            'pager'=> false,
                ));
        $time_max = $valid_time + $self_data['data']['0']['v_in_time'];
        $time = time();
        if(empty($self_data['data']['0']['code']) || empty($self_data['data']['0']['v_in_time'])){
            // $this->exportData(array('msg'=>'系统错误请联系管理员'),0);
            return -1;/*系统错误请联系管理员*/
        }else if($verifies != trim($self_data['data']['0']['code']) || ($time > $time_max))
        {
            // $this->exportData(array('msg'=>'验证码不正确或验证码已过有效期'),0);
            return -2;/*验证码不正确或验证码已过有效期*/
        }else{
            return true;
        }
    }

    // public function editPayPassword($data = array())
    // {
    //    if (!isset($data['u_id']) || intval($data['u_id']) <= 0)
    //    {
    //        return false;
    //    }
    //    $param['u_id'] = intval($data['u_id']);
    //    if (isset($data['u_idcard']) && '' != trim($data['u_idcard'])) ? $param['u_idcard'] = $data['u_idcard'];

    //    if (!empty($param))
    //    {
    //        $param['limit'] = 1;
    //        $param['pager'] = 0;
    //        $info = $this->listData($param);
    //        if (!empty($info['data'][0]))
    //        {
    //            $info = $info['data'][0];

    //            if (isset($data['u_pass']) && ('' == trim($data['u_pass']) ||  $info['u_pass'] != encyptPassword($data['u_pass'])))
    //            {
    //                return false;
    //            }

    //            return $this->updateData(array('u_pass' => encyptPassword($data['new_pass'])), array('u_id' => param['u_id']));
    //        }
    //    }
    //    return false;
    // }

    public function passwordEdit($u_id,$new_pass)
    {
        return $this->updateData(array('u_pass' => encyptPassword($new_pass)), array('u_id' => $u_id));
    }

    /*微信充值模型*/
    public function WXRecharge($RC_log_id,$url_amount)
    {
        if(empty($RC_log_id) || empty($url_amount)){
            return false;
        }
        /*回调地址HOSTURL*/
        ini_set('date.timezone','Asia/Shanghai');
        //error_reporting(E_ERROR);
        require_once WXPAY_PATH."/lib/WxPay.Api.php";
        require_once WXPAY_PATH."/example/WxPay.JsApiPay.php";
        require_once WXPAY_PATH.'/example/log.php';

        //初始化日志
        $logHandler= new \MLIB\WXPAY\CLogFileHandler(WXPAY_PATH."/logs/".date('Y-m-d').'.log');
        $log = \MLIB\WXPAY\Log::Init($logHandler, 15);

        //①、获取用户openid
        $tools = new \MLIB\WXPAY\JsApiPay();
        // $openId = $tools->GetOpenid();
        $openId = 'wx88a7414f850651c8';
        //②、统一下单
        $input = new \MLIB\WXPAY\WxPayUnifiedOrder();
        $input->SetBody("新用工充值");
        $input->SetAttach("test");
        $input->SetOut_trade_no($RC_log_id);/*充值单号*/
        $input->SetTotal_fee(intval($url_amount*100));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url(HOSTURL."/Users/rechargeCallback");
        $input->SetTrade_type("APP");
        $input->SetOpenid($openId);
        $order = \MLIB\WXPAY\WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);

        return $jsApiParameters;

    }
    /*支付宝充值模型*/
    public function alipayRecharge()
    {

    }

}