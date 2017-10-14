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
               $this->exportData( array('msg'=>'图片目录创建失败'),0);
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
                $this->exportData( array('msg'=>'图片写入失败'),0);
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

    //public function editPayPassword($data = array())
    //{
    //    if (!isset($data['u_id']) || intval($data['u_id']) <= 0)
    //    {
    //        return false;
    //    }
    //    $param['u_id'] = intval($data['u_id']);
    //    if (isset($data['u_idcard']) && '' != trim($data['u_idcard'])) ? $param['u_idcard'] = $data['u_idcard'];
    //
    //    if (!empty($param))
    //    {
    //        $param['limit'] = 1;
    //        $param['pager'] = 0;
    //        $info = $this->listData($param);
    //        if (!empty($info['data'][0]))
    //        {
    //            $info = $info['data'][0];
    //
    //            if (isset($data['u_pass']) && ('' == trim($data['u_pass']) ||  $info['u_pass'] != encyptPassword($data['u_pass'])))
    //            {
    //                return false;
    //            }
    //
    //            return $this->updateData(array('u_pass' => encyptPassword($data['new_pass'])), array('u_id' => param['u_id']));
    //        }
    //    }
    //    return false;
    //}

}