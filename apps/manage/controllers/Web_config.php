<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-12 15:53:36
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-20 17:00:44
 */

namespace App\Controller;

class Web_config extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $dao_web_config = new \MDAO\Web_config(array('table'=>'web_config'));
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;

        /*获取配置数组*/
        $arr_config = $dao_web_config ->listData($condition);

        if(isset($arr_config['pager'])){
            $this->myPager($arr_config['pager']);
        }

        $this->tpl->assign('data',$arr_config['data']);
        $this->tpl->display("Web_config/index.html");
    }


     /*处理配置修改数据*/
    public function doWebConfigEdit()
    {
       $jump = "/web_config/index";
        $dao_web_config = new \MDAO\Web_config(array('table'=>'web_config'));

        $wc_id_arr = array();

        isset($_POST['wc_id'])? $wc_id_arr = $_POST['wc_id'] : false;
        $data = array();
        foreach ($wc_id_arr as $key => $value) {

            if(!isset($_POST['key_'.$value]) || empty($_POST['key_'.$value]) ||  empty($value)){
                var_dump($_POST);
                var_dump($_POST['key_'.$value]);
                // msg("参数不足", $status = 0, $jump);
            }else{
                /*判断配置名是否存在*/
                $res = $dao_web_config ->infoData(array('key'=>'wc_name','val'=>$_POST['key_'.$value],'fields'=>'wc_id'));


                if(isset($res['wc_id']) && intval($res['wc_id']) > 0 && $res['wc_id'] != $value){
                    msg("配置名已经存在!", $status = 0, $jump);
                }
            }


            $data['wc_name'] = trim($_POST['key_'.$value]);
            $data['wc_status'] = isset($_POST['status_'.$value])?intval($_POST['status_'.$value]):0;
            $data['wc_value'] = isset($_POST['val_'.$value])?trim($_POST['val_'.$value]):'';
            $data['web_id'] = isset($_POST['web_'.$value])?intval($_POST['web_'.$value]):0;


            $res = $dao_web_config -> updateData($data,array('wc_id' => $value));
        }

        if(!empty($_POST['wc_name'])){
            $dao_Web_config = new \MDAO\Web_config(array('table'=>'web_config'));
                /*判断分类名是否存在*/
                $res = $dao_Web_config ->infoData(array('key'=>'wc_name','val'=>$_POST['wc_name'],'fields'=>'wc_id'));


                if(intval($res) > 0){
                    msg("配置名已经存在!", $status = 0, $jump);
                }


            $data = array();
            $data['wc_name'] = trim($_POST['wc_name']);
            $data['wc_value'] = isset($_POST['wc_value'])?trim($_POST['wc_value']):'';
            $data['web_id'] = isset($_POST['web_id'])?intval($_POST['web_id']):0;
            $data['wc_status'] = isset($_POST['wc_status'])?intval($_POST['wc_status']):0;

            $res = $dao_Web_config ->addData($data);
        }
        $res = $this->createFile();
        if($res){
            msg("配置修改成功", $status = 1, $jump);
        }else{
            msg("配置修改成功,生成文件失败", $status = 0, $jump);
        }
    }


    public function configDel()
    {
        $jump = "/Web_config/index";
        $wc_id = isset($_GET['wc_id']) ? intval($_GET['wc_id']) : 0;
        if($wc_id == 0)
        {
            msg("参数错误,删除失败!", $status = 0, $jump);
        }else{
                $dao_Web_config = new \MDAO\Web_config(array('table'=>'web_config'));

                    $res = $dao_Web_config->delData($wc_id);
                    if($res)
                    {
                        $this->createFile();
                        msg("分类删除成功!", $status = 1, $jump);
                    }else{
                        msg("分类删除失败!", $status = 0, $jump);
                    }
        }

    }

    /*生成配置文件*/
    private function createFile()
    {
        $dao_Web_config = new \MDAO\Web_config(array('table'=>'web_config'));
        $data_0 = $dao_Web_config ->listData(array('pager'=>false,'fields'=>'wc_name,wc_value','wc_status'=>1,'web_id'=>0));

        $res_0 = array();
        foreach ($data_0['data'] as  $v) {
            $res_0["{$v['wc_name']}"] = $v['wc_value'];
        }

        $f_0 = file_put_contents('./configs/web_config.php','<?php $web_config='.var_export($res_0,true).'?>');

        $data_1 = $dao_Web_config ->listData(array('pager'=>false,'fields'=>'wc_name,wc_value','wc_status'=>1,'web_id'=>1));

        $res_1 = array();
        foreach ($data_1['data'] as  $v) {
            $res_1["{$v['wc_name']}"] = $v['wc_value'];
        }

        $f_1 = file_put_contents('../web/configs/web_config.php','<?php $web_config='.var_export($res_1,true).'?>');

        if($f_0 && $f_1)
        {
            return true;
        }else{
            return false;
        }


    }
}