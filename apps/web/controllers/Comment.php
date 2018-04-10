<?php
/**
 * 评价接口
 */
namespace App\Controller;

class Comment extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        
    }

    /*充值结束*/
    /*评价添加接口*/
    public function commentAdd()
    {
        $data_c = array();
        if(empty($_REQUEST['t_id']) || empty($data_c['t_id'] = intval($_REQUEST['t_id']))){
            $this->exportData( array('msg'=>'任务id为空'),0);
        }
        if(empty($_REQUEST['u_id']) || empty($data_c['u_id'] = intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'评论人id为空'),0);
        }
        if(empty($_REQUEST['tc_u_id']) || empty($data_c['tc_u_id'] = intval($_REQUEST['tc_u_id']))){
            $this->exportData( array('msg'=>'评论人id为空'),0);
        }

        $data_c['tc_start'] = $data_c['tc_first_start'] = isset($_REQUEST['tc_start']) ? intval($_REQUEST['tc_start']) : 0;
        $data_c['tc_type'] = isset($_REQUEST['tc_type']) ? intval($_REQUEST['tc_type']) : 0;
        $data_c['tc_last_edit_time'] = time();
        $data_c['tc_in_time'] = time();
        $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
        $tc_id = $dao_task_comment -> addData($data_c);
        /*设置users好评次数*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        switch ($data_c['tc_start']) {
            case '3':
                $sql = 'update users set u_high_opinions = u_high_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;
            case '2':
                $sql = 'update users set u_middle_opinions = u_middle_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;
            case '1':
                $sql = 'update users set u_low_opinions = u_low_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;

            default:
                // $sql = 'update users set u_high_opinions = u_high_opinions + 1 where u_id = ' . $data_c['tc_u_id'];
                break;
        }

        $result = $dao_users ->queryData($sql);


        $data_ext = array();
        isset($_REQUEST['tce_desc']) ? $data_ext['tce_desc'] = trim($_REQUEST['tce_desc']) : false ;
        if(!empty($data_ext) && intval($tc_id) > 0) {
            $data_ext['tc_id'] = intval($tc_id);
            $dao_task_comment_ext = new \WDAO\Users(array('table'=>'task_comment_ext'));
            $dao_task_comment_ext -> addData($data_ext);
        }
        if(intval($tc_id) > 0){
           $this->exportData( array('data'=>array('tc_id'=>$tc_id)),1);
        }else{
           $this->exportData( array('msg'=>"评价失败"),0);
        }
    }
    /**********************************************************添加好评次数**********************************************************/
    /*评价修改接口*/
    // public function commentEdit()
    // {
    //     $data_c = array();
    //     if(empty($_REQUEST['tc_id']) || empty($tc_id = intval($_REQUEST['tc_id']))){
    //         $this->exportData( array('msg'=>'评价id为空'),0);
    //     }

    //     isset($_REQUEST['tc_start']) ? $data_c['tc_start'] = intval($_REQUEST['tc_start']) : fales;
    //     $data_c['tc_last_edit_time'] = time();
    //     $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
    //     $res = $dao_task_comment -> updateData($data_c,array('tc_id' => $tc_id));
    //     $data_ext = array();
    //     $data_ext['tce_desc'] = isset($_REQUEST['tce_desc']) ? trim($_REQUEST['tce_desc']) : '' ;
    //     if($res) {
    //         $data_ext['tc_id'] = intval($tc_id);
    //         $dao_task_comment_ext = new \WDAO\Users(array('table'=>'task_comment_ext'));
    //         $dao_task_comment_ext -> updateData($data_ext,array('tc_id' => $tc_id));
    //     }
    //     $sql = 'update task_comment set tc_edit_times = tc_edit_times + 1 where tc_id = ' . $tc_id;
    //     $result = $dao_task_comment ->queryData($sql);
    //     if($res){
    //        $this->exportData( array('msg'=>'修改评论成功'),1);
    //     }
    // }

    /*查看自己评论他人接口列表*/
    public function userCommentOther()
    {
        $data = array();
        if(empty($_REQUEST['u_id']) || empty($data['u_id'] =  intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户id为空'),0);
        }
        if(isset($_REQUEST['page']) && !empty(intval($_REQUEST['page']))) {
            $data['pager'] = true;
            $data['page'] = intval($_REQUEST['page']) ;
        }else{
            $data['pager'] = false;
        }
        $data['leftjoin'] = array('task_comment_ext','task_comment.tc_id=task_comment_ext.tc_id');
        $data['fields'] = 'task_comment.tc_id as tc_id,t_id,tc_u_id,tc_in_time,tc_start,task_comment_ext.tce_desc';
        $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
        $list = $dao_task_comment ->listData($data);
        foreach ($list['data'] as $k => &$v) {
            $v['u_img'] = $this-> getHeadById($v['tc_u_id']);
        }
        unset($list['pager']);
        $this->exportData( $list,1);
    }

    /*查看他人评论自己接口列表*/
    public function otherCommentUser()
    {
        $data = array();
        if(empty($_REQUEST['tc_u_id']) || empty($data['tc_u_id'] =  intval($_REQUEST['tc_u_id']))){
            $this->exportData( array('msg'=>'用户id为空'),0);
        }
        if(isset($_REQUEST['page']) && !empty(intval($_REQUEST['page']))) {
            $data['pager'] = true;
            $data['page'] = intval($_REQUEST['page']) ;
        }else{
            $data['pager'] = false;
        }
        $data['leftjoin'] = array('task_comment_ext','task_comment.tc_id=task_comment_ext.tc_id');
        $data['fields'] = 'task_comment.tc_id as tc_id,u_id,t_id,tc_u_id,tc_in_time,tc_start,task_comment_ext.tce_desc';
        $dao_task_comment = new \WDAO\Users(array('table'=>'task_comment'));
        $list = $dao_task_comment ->listData($data);
        foreach ($list['data'] as $k => &$v) {
            $v['u_img'] = $this-> getHeadById($v['u_id']);
        }
        unset($list['pager']);
        $this->exportData( $list,1);
    }

}