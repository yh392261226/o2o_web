<?php
//站内信息
namespace App\Controller;

class Msg extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    /**
     * 用户站内标题信息
     */
    public function msgList($value='')
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户id不能为空'),0);
        }
        $page = isset($_GET['page']) && !empty(intval($_GET['page'])) ? intval($_GET['page']) : 1;
        $wm_type = isset($_GET['wm_type']) ? intval($_GET['wm_type']) : 3;

        $time = time();
        $where =  'um_status != -1
        AND (web_msg.wm_start_time <= '.$time.' OR web_msg.wm_start_time = 0 )
        AND (web_msg.wm_end_time >= '.$time.' OR web_msg.wm_end_time = 0)
        AND wm_status = 1
        AND user_msg.u_id='.$u_id;
        switch ($wm_type) {
            case 0:
                $where .= ' AND web_msg.wm_type = 0';
                break;
            case 1:
                $where .= ' AND web_msg.wm_type = 1';
                break;
            case 2:
                $where .= ' AND web_msg.wm_type = 2';
                break;
            default:
                $where .= ' ';
                break;
        }

        $dao_web_msg = new \WDAO\Users(array('table'=>'web_msg'));
        $msg_list = $dao_web_msg ->listData(array(
            'pager' => true,
            'page' => $page,
            'where' => $where,
            'fields' => 'web_msg.wm_title,user_msg.um_in_time,web_msg.wm_type,web_msg.wm_id ,user_msg.um_id,web_msg_ext.wm_desc,user_msg.um_status',
            'join' => array('user_msg','web_msg.wm_id=user_msg.wm_id '),
            'leftjoin' => array('web_msg_ext','web_msg.wm_id=web_msg_ext.wm_id '),
            'order' => 'user_msg.um_in_time desc,user_msg.um_status asc',
            ));
        unset($msg_list['pager']);
        $this->exportData( $msg_list,1);
    }

    /*删除站内信息*/
    public function msgDel()
    {

        if(empty($_GET['um_id']) || empty($um_id = intval($_GET['um_id']))){
            $this->exportData( array('msg'=>'用户站内信关系ID为空'),0);
        }

        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $res = $dao_user_msg ->updateData(array(
            'um_status' => '-1',
            ),array('um_id'=>$um_id));
        if($res){
            $this->exportData( array('msg'=>'信息删除成功'),1);
        }else{
            $this->exportData( array('msg'=>'信息删除失败'),0);
        }


    }

    /*修改信息读取状态*/
    public function msgReadEdit()
    {

        if(empty($_GET['um_id']) || empty($um_id = intval($_GET['um_id']))){
            $this->exportData( array('msg'=>'用户站内信关系ID为空'),0);
        }

        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $res = $dao_user_msg ->updateData(array('um_status' => '1',),array('um_id'=>$um_id));
        if($res){
            $this->exportData( array('msg'=>'状态修改成功'),1);
        }else{
            $this->exportData( array('msg'=>'状态修改失败'),0);
        }
    }

    /*站内信详细信息*/
    public function msgInfo()
    {

        if(empty($_GET['um_id']) || empty($um_id = intval($_GET['um_id']))){
            $this->exportData( array('msg'=>'用户站内信关系ID为空'),0);
        }
        /*修改状态*/
        $dao_user_msg = new \WDAO\Users(array('table'=>'user_msg'));
        $msg_list = $dao_user_msg ->updateData(array(
            'um_status' => '1',
            ),array('um_id'=>$um_id));
        $wm_id = 0;
        $wm_id_arr = $dao_user_msg ->infoData(array('key'=>'um_id','val'=>$um_id,'fields'=>'wm_id,um_id'));
        if(isset($wm_id_arr['wm_id'])){
            $wm_id = $wm_id_arr['wm_id'];
        }

        /*获取内容*/
        $info = array();
        if(!empty($wm_id)){
            $dao_web_msg = new \WDAO\Users(array('table'=>'web_msg'));

            $info = $dao_web_msg -> listData(array(
                'where' => 'web_msg.wm_id='.$wm_id,
                'wm_status' => 1,
                'fields'=>'wm_title,wm_in_time,wm_desc,web_msg_ext.wm_id',
                'leftjoin' => array('web_msg_ext','web_msg.wm_id=web_msg_ext.wm_id '),
                ));
            unset($info['pager']);

        }
        $this->exportData( $info,1);
    }

}