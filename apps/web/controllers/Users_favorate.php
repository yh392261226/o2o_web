<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-19 17:06:32
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-19 18:26:50
 */

namespace App\Controller;

class Users_favorate extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }


    public function users()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $favorate_arr = $dao_favorate -> listData(array('users_favorate.u_id'=>$u_id,'f_type'=>1,'join'=>array('users',"users.u_id = users_favorate.f_type_id"),'fields'=>'users.u_id,users.u_sex,users.u_online,users.u_start,users.u_worked_num,f_id','pager'=>false));
        /*获取分类数组*/
        $this->exportData( $favorate_arr,1);
    }


/**********************************************************任务部分******************************************************************/



    /*任务列表*/
    public function tasks()
    {
        $u_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : 0;
        if(empty($u_id)){
            $this->exportData( array('msg'=>'请输入用户id'),0);
        }

        $dao_favorate = new \WDAO\Users_favorate(array('table'=>'users_favorate'));
        $favorate_arr = $dao_favorate -> listData(array('users_favorate.u_id'=>$u_id,'f_type'=>0,'join'=>array('tasks',"tasks.t_id = users_favorate.f_type_id"),'fields'=>'tasks.t_title,tasks.t_amount,tasks.t_duration,tasks.t_author,tasks.t_status','pager'=>false));
        $this->exportData( $favorate_arr,1);
    }





















}
