<?php
/**
 * 投诉信息
 */
namespace App\Controller;

class Complaints extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        
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

}