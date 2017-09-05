<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-04 17:56:51
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-04 18:31:59
 */
namespace MDAO;

class Advertising extends \MDAOBASE\DaoBase
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /*获取文章列表*/
    public function advertisingList($condition=array())
    {
        $a_id_arr = array();
        $page = $condition['page'];
        $info = array();
        $info = array(
            'pager'=>true,'page'=>$page,
            'fields'=>'a_id,a_title,a_info,managers.m_name as a_author,a_last_edit_time' ,
            'leftjoin'=>array('managers',"managers.m_id = advertising.a_author"),
            );

       if(!empty($condition['search_condition'])){

            $info['a_title'] =  array('type' => 'like', 'value' => $condition['search_condition']);

       }
            /*获取所有文章信息*/
            $a_id_arr = $this->listData($info);

       return $a_id_arr;
    }









}