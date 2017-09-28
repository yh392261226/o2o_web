<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-12 15:53:36
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-28 15:46:33
 */

namespace App\Controller;

class Application_config extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $dao_application_config = new \MDAO\Application_config(array('table'=>'application_config'));
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;

        /*获取配置数组*/
        $arr_config = $dao_application_config ->listData($condition);

        if(isset($arr_config['pager'])){
            $this->myPager($arr_config['pager']);
        }

        $this->tpl->assign('data',$arr_config['data']);
        $this->tpl->display("Application_config/index.html");
    }


     /*处理配置修改数据*/
    public function doWebConfigEdit()
    {
       $jump = "/Application_config/index";
        $dao_application_config = new \MDAO\Application_config(array('table'=>'application_config'));

        $ac_id_arr = array();

        isset($_POST['ac_id'])? $ac_id_arr = $_POST['ac_id'] : false;
        $data = array();
        foreach ($ac_id_arr as $key => $value) {

            if(!isset($_POST['key_'.$value]) || empty($_POST['key_'.$value]) ||  empty($value)){

                msg("参数不足", $status = 0, $jump);
            }else{
                /*判断配置名是否存在*/
                $res = $dao_application_config ->infoData(array('key'=>'ac_name','val'=>$_POST['key_'.$value],'fields'=>'ac_id'));


                if(isset($res['ac_id']) && intval($res['ac_id']) > 0 && $res['ac_id'] != $value){
                    msg("配置名已经存在!", $status = 0, $jump);
                }
            }


            $data['ac_name'] = trim($_POST['key_'.$value]);
            $data['ac_status'] = isset($_POST['status_'.$value])?intval($_POST['status_'.$value]):0;
            $data['ac_value'] = isset($_POST['val_'.$value])?trim($_POST['val_'.$value]):'';
            $data['app_id'] = isset($_POST['app_'.$value])?intval($_POST['app_'.$value]):0;


            $res = $dao_application_config -> updateData($data,array('ac_id' => $value));
        }

        if(!empty($_POST['ac_name'])){
            $dao_application_config = new \MDAO\Application_config(array('table'=>'application_config'));
                /*判断分类名是否存在*/
                $res = $dao_application_config ->infoData(array('key'=>'ac_name','val'=>$_POST['ac_name'],'fields'=>'ac_id'));


                if(intval($res) > 0){
                    msg("配置名已经存在!", $status = 0, $jump);
                }


            $data = array();
            $data['ac_name'] = trim($_POST['ac_name']);
            $data['ac_value'] = isset($_POST['ac_value'])?trim($_POST['ac_value']):'';
            $data['app_id'] = isset($_POST['app_id'])?intval($_POST['app_id']):0;
            $data['ac_status'] = isset($_POST['ac_status'])?intval($_POST['ac_status']):0;

            $res = $dao_application_config ->addData($data);
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
        $jump = "/Application_config/index";
        $ac_id = isset($_GET['ac_id']) ? intval($_GET['ac_id']) : 0;
        if($ac_id == 0)
        {
            msg("参数错误,删除失败!", $status = 0, $jump);
        }else{
                $dao_application_config = new \MDAO\Application_config(array('table'=>'application_config'));

                    $res = $dao_application_config->delData($ac_id);
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
        $dao_application_config = new \MDAO\application_config(array('table'=>'application_config'));
        $data = $dao_application_config ->listData(array('pager'=>false,'fields'=>'ac_name,ac_value','ac_status'=>1));

        $res = array();
        foreach ($data['data'] as  $v) {
            $res["{$v['ac_name']}"] = $v['ac_value'];
        }

        return  file_put_contents('../web/configs/application_config.php','<?php $application_config='.var_export($res,true).'?>');

    }
}