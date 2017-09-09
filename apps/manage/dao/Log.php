<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-09 15:40:30
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-09 15:41:03
 */
namespace MDAO;

class Log extends \MDAOBASE\DaoBase
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /*获取投诉列表*/
    public function getComplaintsList($condition=array())
    {
        $data = array();
        $page = $condition['page'];
        $info = array();
        $info = array(
            'pager'=>true,'page'=>$page,
            'fields'=>'c_id,c_author,c_title,c_against,c_in_time,c_status,complaints_type.ct_name as ct_name,complaints.ct_id as ct_id ,c_last_edit_time,c_last_editor' ,
            );

       if($condition['ct_id'] > 0)
       {
            /*获取文章信息*/
            $info['where'] = 'complaints.ct_id="'.$condition['ct_id'].'"' ;


       }

       if($condition['c_status'] > -1)
       {
            $info['c_status'] =  $condition['c_status'];


       }

       if(!empty($condition['search_condition'])){

            $info['c_title'] =  array('type' => 'like', 'value' => $condition['search_condition']);

       }
       // var_dump($info);die;
       $info['leftjoin'] = array('complaints_type',"complaints_type.ct_id = complaints.ct_id");
            /*获取所有文章信息*/
            $data = $this->listData($info);


       return $data;
    }
}