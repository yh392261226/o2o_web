<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-09 14:37:08
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-09 18:23:47
 */

namespace App\Controller;

class Log extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function orders()
    {
        $dao_log = new \MDAO\Log(array('table'=>'orders_log'));
        $data = array();
        if (!empty($_REQUEST['start_time'])) $data['ol_in_time'][] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_time']));
        if (!empty($_REQUEST['end_time'])) $data['ol_in_time'][] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_time']));
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time']) && $_REQUEST['start_time'] != 0 && $_REQUEST['end_time'] != 0 && strtotime($_REQUEST['end_time']) < strtotime($_REQUEST['start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }

        $data['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
        $data['leftjoin'] = array('managers',"managers.m_id = orders_log.ol_manager");
        $data['fields'] = 'ol_id,o_id,t_id,ol_remark,ol_in_time,managers.m_name as ol_manager';



        /*获取技能数组*/
        $arr_order = $dao_log ->listData($data);


        if(isset($arr_order['pager'])){
            $this->myPager($arr_order['pager']);
        }


        if(!empty($_REQUEST['start_time']))
        {
            $this->tpl->assign("start_time",$_REQUEST['start_time']);
        }
        if(!empty($_REQUEST['end_time']))
        {
            $this->tpl->assign("end_time",$_REQUEST['end_time']);
        }

        $this->tpl->assign('data',$arr_order['data']);
        $this->tpl->display("Log/orders.html");
    }


    public function platformFunds()
    {
        $dao_log = new \MDAO\Log(array('table'=>'platform_funds_log'));
        $data = array();
/*时间区间*/
        if (!empty($_REQUEST['start_time'])) $data['pfl_in_time'][] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_time']));
        if (!empty($_REQUEST['end_time'])) $data['pfl_in_time'][] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_time']));
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time']) && $_REQUEST['start_time'] != 0 && $_REQUEST['end_time'] != 0 && strtotime($_REQUEST['end_time']) < strtotime($_REQUEST['start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }
/*金额区间*/
        if (!empty($_REQUEST['start_amount'])) $data['pfl_amount'][] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_amount']));
        if (!empty($_REQUEST['end_amount'])) $data['pfl_amount'][] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_amount']));
        if (!empty($_REQUEST['start_amount']) && !empty($_REQUEST['end_amount']) && $_REQUEST['start_amount'] != 0 && $_REQUEST['end_amount'] != 0 && intval($_REQUEST['end_amount']) < intval($_REQUEST['start_amount']))
        {
            //结束时间不能小于开始时间
            msg('金额区间填写错误', 0);
        }

/*状态选择*/
        if(isset($_REQUEST['pfl_status']) && $_REQUEST['pfl_status'] !== '')  $data['pfl_status'] = intval($_REQUEST['pfl_status']);

        $data['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
        $data['leftjoin'] = array('managers',"managers.m_id = platform_funds_log.pfl_last_editor");
        $data['fields'] = 'pfl_id,t_id,o_id,pfl_amount,pfl_in_time,pfl_reason,pfl_status,pfl_last_edit_time,managers.m_name as pfl_last_editor';


        /*获取技能数组*/
        $arr_order = $dao_log ->listData($data);
        // var_dump($arr_order);die;


        if(isset($arr_order['pager'])){
            $this->myPager($arr_order['pager']);
        }


        if(!empty($_REQUEST['start_time']))
        {
            $this->tpl->assign("start_time",$_REQUEST['start_time']);
        }
        if(!empty($_REQUEST['end_time']))
        {
            $this->tpl->assign("end_time",$_REQUEST['end_time']);
        }

        if(!empty($_REQUEST['start_amount']))
        {
            $this->tpl->assign("start_amount",$_REQUEST['start_amount']);
        }
        if(!empty($_REQUEST['end_amount']))
        {
            $this->tpl->assign("end_amount",$_REQUEST['end_amount']);
        }

        if(isset($_REQUEST['pfl_status'])&& $_REQUEST['pfl_status'] !== '')
        {
            $this->tpl->assign("pfl_status",$_REQUEST['pfl_status']);
        }else{
            $this->tpl->assign("pfl_status",-100);
        }

        $this->tpl->assign('data',$arr_order['data']);
        $this->tpl->display("Log/platformFunds.html");
    }


    public function userRecharge()
    {
        $dao_log = new \MDAO\Log(array('table'=>'user_recharge_log'));
        $data = array();
/*时间区间*/
        if (!empty($_REQUEST['start_time'])) $data['url_in_time'][] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_time']));
        if (!empty($_REQUEST['end_time'])) $data['url_in_time'][] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_time']));
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time']) && $_REQUEST['start_time'] != 0 && $_REQUEST['end_time'] != 0 && strtotime($_REQUEST['end_time']) < strtotime($_REQUEST['start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }
/*金额区间*/
        if (!empty($_REQUEST['start_amount'])) $data['url_amount'][] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_amount']));
        if (!empty($_REQUEST['end_amount'])) $data['url_amount'][] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_amount']));
        if (!empty($_REQUEST['start_amount']) && !empty($_REQUEST['end_amount']) && $_REQUEST['start_amount'] != 0 && $_REQUEST['end_amount'] != 0 && intval($_REQUEST['end_amount']) < intval($_REQUEST['start_amount']))
        {
            //结束时间不能小于开始时间
            msg('金额区间填写错误', 0);
        }

/*状态选择*/
        if(isset($_REQUEST['u_id']) && $_REQUEST['u_id'] !== '')  $data['u_id'] = intval($_REQUEST['u_id']);

        $data['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
        $data['leftjoin'] = array('payments',"payments.p_id = user_recharge_log.p_id");
        $data['fields'] = 'url_id,u_id,url_amount,payments.p_name as p_id,url_in_time,url_status,url_solut_time,url_solut_author,url_truename,url_card,url_remark';


        /*获取技能数组*/
        $arr_order = $dao_log ->listData($data);
        // var_dump($arr_order);die;


        if(isset($arr_order['pager'])){
            $this->myPager($arr_order['pager']);
        }


        if(!empty($_REQUEST['start_time']))
        {
            $this->tpl->assign("start_time",$_REQUEST['start_time']);
        }
        if(!empty($_REQUEST['end_time']))
        {
            $this->tpl->assign("end_time",$_REQUEST['end_time']);
        }

        if(!empty($_REQUEST['start_amount']))
        {
            $this->tpl->assign("start_amount",$_REQUEST['start_amount']);
        }
        if(!empty($_REQUEST['end_amount']))
        {
            $this->tpl->assign("end_amount",$_REQUEST['end_amount']);
        }

        if(isset($_REQUEST['u_id'])&& $_REQUEST['u_id'] !== '')
        {
            $this->tpl->assign("u_id",$_REQUEST['u_id']);
        }

        $this->tpl->assign('data',$arr_order['data']);
        $this->tpl->display("Log/userRecharge.html");
    }







}