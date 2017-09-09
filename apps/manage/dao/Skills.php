<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-08 14:38:36
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-08 14:40:43
 */

namespace MDAO;

class Skills extends \MDAOBASE\DaoBase
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /*获取投诉列表*/
    public function getSkillsList($condition=array())
    {
        $data = array();
        $page = $condition['page'];
        $info = array();
        $info = array(
            'pager'=>true,'page'=>$page,
            'fields'=>'c_id,c_author,c_title,c_against,c_in_time,c_status,Skills_type.ct_name as ct_name,Skills.ct_id as ct_id ,c_last_edit_time,c_last_editor' ,
            );

       if($condition['ct_id'] > 0)
       {
            /*获取文章信息*/
            $info['where'] = 'Skills.ct_id="'.$condition['ct_id'].'"' ;


       }

       if($condition['c_status'] > -1)
       {
            $info['c_status'] =  $condition['c_status'];


       }

       if(!empty($condition['search_condition'])){

            $info['c_title'] =  array('type' => 'like', 'value' => $condition['search_condition']);

       }
       // var_dump($info);die;
       $info['leftjoin'] = array('Skills_type',"Skills_type.ct_id = Skills.ct_id");
            /*获取所有文章信息*/
            $data = $this->listData($info);


       return $data;
    }
}