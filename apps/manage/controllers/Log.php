<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-09 14:37:08
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-10-23 10:11:23
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
        $data['fields'] = 'pfl_id,pfl_type,pfl_type_id,pfl_amount,pfl_in_time,pfl_reason,pfl_status,pfl_last_edit_time,managers.m_name as pfl_last_editor';


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
        if(isset($_REQUEST['url_status']) && $_REQUEST['url_status'] !== '')  $data['url_status'] = intval($_REQUEST['url_status']);

/*用户id*/
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

        if(isset($_REQUEST['url_status']) && $_REQUEST['url_status'] !== '')
        {
            $this->tpl->assign("url_status",$_REQUEST['url_status']);
        }else{
            $this->tpl->assign("url_status",-100);
        }

        $this->tpl->assign('data',$arr_order['data']);
        $this->tpl->display("Log/userRecharge.html");
    }

    /*人工修改充值状态*/
    public function changeRechargeStatus()
    {
        if(isset($_REQUEST['url_id']) && !empty($url_id = intval($_REQUEST['url_id'])) && isset($_REQUEST['url_status']) && (!empty(intval($_REQUEST['url_status'] || $_REQUEST['url_status'] === '0')))){
            $dao_user_recharge_log = new \MDAO\Log(array('table'=>'user_recharge_log'));
            $url_status = intval($_REQUEST['url_status']);
            $info = $dao_user_recharge_log ->infoData(array('key'=>'url_id','val'=>$url_id));
            if($info['url_status'] == '1'){
                echo json_encode(array('msg' => '确认成功后不可修改状态', 'status' => 0));exit;
            }else{
                $dao_web_users = new \MDAO\Users(array('table'=>'users'));
                $dao_users_ext_funds = new \MDAO\Users_ext_funds();
                /*获取用户当前余额*/
                $user_url_overage = $dao_users_ext_funds ->infoData(array('key'=>'u_id','val'=>$info['u_id']));
                $url_overage = $user_url_overage['uef_overage'] + $info['url_amount'];


                $recharge_res = $dao_user_recharge_log ->updateData(array('url_status'=>$url_status,'url_overage'=>$url_overage,'url_solut_author'=>parent::$manager_status,'url_solut_time'=>time()),array('url_id'=>$url_id));
                if($url_status == 1 && $recharge_res){

                    $data['u_id'] = $info['u_id'];
                    $data['pfl_amount'] = $info['url_amount'];
                    $data['pfl_type_id'] = $info['url_id'];
                    $data['pfl_last_editor'] = parent::$manager_status;
                    $res = $dao_web_users ->judgeReChargeRes($data);

                }
            }
            echo json_encode(array('msg' => '修改成功', 'status' => 1));exit;

        }else{
            echo json_encode(array('msg' => '参数不足操作失败', 'status' => 0));exit;
        }
    }

    /*修改充值备注*/
    public function changeRechargeRemark()
    {
        if(isset($_REQUEST['url_id']) && !empty($url_id = intval($_REQUEST['url_id'])) && isset($_REQUEST['url_remark'])){
            $dao_user_recharge_log = new \MDAO\Log(array('table'=>'user_recharge_log'));
            $url_remark = trim($_REQUEST['url_remark']);
            $res = $dao_user_recharge_log -> updateData(array('url_remark' =>$url_remark),array('url_id'=>$url_id));
            if($res){
                echo json_encode(array('msg' => '修改成功', 'status' => 1));exit;
            }else{
                echo json_encode(array('msg' => '修改失败', 'status' => 0));exit;
            }

        }else{
            echo json_encode(array('msg' => '参数不足操作失败', 'status' => 0));exit;
        }
    }

    public function userWithdrawProof()
    {
        if (isset($_REQUEST['uwl_id']) && intval($_REQUEST['uwl_id']) > 0 && isset($_REQUEST['uwl_proof']) && intval($_REQUEST['uwl_proof']) > 0)
        {
            $dao_log_ext = new \MDAO\Log(array('table'=>'user_withdraw_log_ext'));
            $result = $dao_log_ext->updateData(array('uwl_proof' => trim($_REQUEST['uwl_proof'])), array('uwl_id' => intval($_REQUEST['uwl_id'])));
            if ($result)
            {
                $dao_log = new \MDAO\Log(array('table'=>'user_withdraw_log'));
                $dao_log->updateData(array('uwl_status' => 2), array('uwl_id' => intval($_REQUEST['uwl_id'])));
            }
            if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'])
            {
                if (!$result)
                {
                    echo json_encode(array('msg' => '操作失败', 'status' => 0));exit;
                }
                echo json_encode(array('msg' => '操作成功', 'status' => 1));exit;
            }
            else
            {
                if (!$result)
                {
                    msg('操作失败', 0);
                }
                msg('操作成功', 1);
            }
        }
    }

    public function userWithdraw()
    {
        $dao_log = new \MDAO\Log(array('table'=>'user_withdraw_log'));
        $data = array();
/*时间区间*/
        if (!empty($_REQUEST['start_time'])) $data['uwl_in_time'][] = array('type' => 'ge', 'ge_value' => strtotime($_REQUEST['start_time']));
        if (!empty($_REQUEST['end_time'])) $data['uwl_in_time'][] = array('type' => 'le', 'le_value' => strtotime($_REQUEST['end_time']));
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time']) && $_REQUEST['start_time'] != 0 && $_REQUEST['end_time'] != 0 && strtotime($_REQUEST['end_time']) < strtotime($_REQUEST['start_time']))
        {
            //结束时间不能小于开始时间
            msg('结束时间不能小于开始时间', 0);
        }
/*金额区间*/
        if (!empty($_REQUEST['start_amount'])) $data['uwl_amount'][] = array('type' => 'ge', 'ge_value' => intval($_REQUEST['start_amount']));
        if (!empty($_REQUEST['end_amount'])) $data['uwl_amount'][] = array('type' => 'le', 'le_value' => intval($_REQUEST['end_amount']));
        if (!empty($_REQUEST['start_amount']) && !empty($_REQUEST['end_amount']) && $_REQUEST['start_amount'] != 0 && $_REQUEST['end_amount'] != 0 && intval($_REQUEST['end_amount']) < intval($_REQUEST['start_amount']))
        {
            //结束时间不能小于开始时间
            msg('金额区间填写错误', 0);
        }

/*状态选择*/
        if(isset($_REQUEST['uwl_status']) && $_REQUEST['uwl_status'] !== '')  $data['uwl_status'] = intval($_REQUEST['uwl_status']);

/*用户id*/
        if(isset($_REQUEST['u_id']) && $_REQUEST['u_id'] !== '')  $data['u_id'] = intval($_REQUEST['u_id']);

        $data['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
        $data['leftjoin'] = array('user_withdraw_log_ext',"user_withdraw_log.uwl_id = user_withdraw_log_ext.uwl_id");
        $data['fields'] = 'user_withdraw_log.uwl_id as uwl_id,u_id,uwl_in_time,uwl_solut_time,uwl_solut_author,uwl_status,uwl_amount,p_id,uwl_truename,uwl_card,uwl_proof,uwl_remark,uwl_openarea';




        /*获取技能数组*/
        $arr_order = $dao_log ->listData($data);

        /*获取所有p_id集合*/
        $p_id_arr = array();
        foreach ($arr_order['data'] as $key => $value) {
           $p_id_arr[] = $value['p_id'];
        }
        if(!empty($p_id_arr)){

            $p_id_arr = array_unique($p_id_arr);

            $p_id_str = implode(',',$p_id_arr);

            $dao_payments = new \MDAO\Log(array('table'=>'payments'));
            $payments_arr = $dao_payments ->listData(array('pager'=>false,'p_id'=>array('type'=>'in','value'=>$p_id_str),'fields'=>'p_id,p_name'));


            foreach ($arr_order['data'] as $key => &$value) {
                foreach ($payments_arr['data'] as $k => $v) {

                    if(isset($value['p_id'])&&isset($v['p_id'])){

                        if($value['p_id'] == $v['p_id']){

                        $value['p_id']  = $v['p_name'];

                        break;
                        }
                    }

                }
            }
        }

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

        if(isset($_REQUEST['uwl_status'])&& $_REQUEST['uwl_status'] !== '')
        {
            $this->tpl->assign("uwl_status",$_REQUEST['uwl_status']);
        }else{
            $this->tpl->assign("uwl_status",-100);
        }

        $this->tpl->assign('data',$arr_order['data']);
        $this->tpl->display("Log/userWithdraw.html");
    }
/*用户位置记录*/

    public function userCurPosition()
    {
        $dao_log = new \MDAO\Log(array('table'=>'users_cur_position'));
        $data = array();

/*用户id*/
        if(isset($_REQUEST['u_id']) && $_REQUEST['u_id'] !== '')  $data['u_id'] = intval($_REQUEST['u_id']);

        $data['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;

        /*获取位置数组*/
        $arr_order = $dao_log ->listData($data);

        if(isset($arr_order['pager'])){
            $this->myPager($arr_order['pager']);
        }

        if(isset($_REQUEST['u_id'])&& $_REQUEST['u_id'] !== '')
        {
            $this->tpl->assign("u_id",$_REQUEST['u_id']);
        }

        $this->tpl->assign('data',$arr_order['data']);
        $this->tpl->display("Log/userCurPosition.html");
    }

    public function withdrawAudit()
    {
        $uwl_id = isset($_REQUEST['uwl_id']) && intval($_REQUEST['uwl_id']) > 0 ? intval($_REQUEST['uwl_id']) : 0;
        $is_ajax = isset($_REQUEST['is_ajax']) ? intval($_REQUEST['is_ajax']) : 0;
        $uwl_status = isset($_REQUEST['uwl_status']) && in_array($_REQUEST['uwl_status'], array('-1', '1')) ? trim($_REQUEST['uwl_status']) : '';
        $user_funds = new \MDAO\Users_ext_funds();
        if (!$uwl_id || '' == $uwl_status)
        {
            if (!$is_ajax)
            {
                return 0;
            }
            echo 0;exit;
        }
        $dao_log = new \MDAO\Log(array('table'=>'user_withdraw_log'));
        $result = $dao_log->updateData(array('uwl_status' => $uwl_status), array('uwl_id' => $uwl_id));
        if (!$result)
        {
            if (!$is_ajax)
            {
                return 0;
            }
            echo 0;exit;
        }
        //$this->sendWithdrawMessage();
        if ($uwl_status == -1)
        {
            $info = $dao_log->infoData($uwl_id);
            if (!empty($info))
            {
                $platform_funds = new \MDAO\Platform_funds_log();
                $curtime = time();
                $pf_result = $platform_funds->addData(array(
                    'pfl_type'    => 1,
                    'pfl_type_id' => $uwl_id,
                    'pfl_amount'  => ($info['uwl_amount'] * -1),
                    'pfl_in_time' => $curtime,
                    'pfl_reason'  => 'withdraw',
                    'pfl_status'  => 2,
                    'pfl_last_editor' => parent::$manager_status,
                    'pfl_last_edit_time' => $curtime,
                ));
                if ($pf_result)
                {


                    $sql = 'insert into users_ext_funds (u_id, uef_overage) values (' . $info['u_id'] . ', ' . $info['uwl_amount'] . ') ON DUPLICATE KEY update uef_overage = uef_overage + ' . $info['uwl_amount'] . ',u_id='. $info['u_id'];

                    $user_funds->queryData($sql);
                }
            }
        }
        /*获取余额*/
        $info = $dao_log->infoData($uwl_id);
        $user_url_overage = $user_funds ->infoData(array('key'=>'u_id','val'=>$info['u_id']));
        $url_overage = 0;
        if(isset($user_url_overage['uef_overage'])){
            $url_overage = $user_url_overage['uef_overage'];
        }
        $result = $dao_log->updateData(array('uwl_overage'=>$url_overage), array('uwl_id' => $uwl_id));
        if (!$is_ajax)
        {
            return 1;
        }
        echo 1;exit;
    }

    public function withdrawRecord()
    {
        $uwl_id = isset($_REQUEST['uwl_id']) && intval($_REQUEST['uwl_id']) > 0 ? intval($_REQUEST['uwl_id']) : 0;
        $is_ajax = isset($_REQUEST['is_ajax']) ? intval($_REQUEST['is_ajax']) : 0;
        $uwl_remark = isset($_REQUEST['uwl_remark']) ? trim($_REQUEST['uwl_remark']) : '';
        $uwl_proof = isset($_REQUEST['uwl_proof']) ? trim($_REQUEST['uwl_proof']) : '';
        if (!$uwl_id)
        {
            if (!$is_ajax)
            {
                return 0;
            }
            echo 0;exit;
        }
        $dao_log_ext = new \MDAO\Log(array('table'=>'user_withdraw_log_ext'));
        $result = $dao_log_ext->updateData(array('uwl_remark' => $uwl_remark, 'uwl_proof' => $uwl_proof), array('uwl_id' => $uwl_id));
        if (!$result)
        {
            if (!$is_ajax)
            {
                return 0;
            }
            echo 0;exit;
        }
        $dao_log = new \MDAO\Log(array('table'=>'user_withdraw_log'));
        $dao_log->updateData(array('uwl_status' => 2), array('uwl_id' => $uwl_id));
        //$this->sendWithdrawMessage();
        $info = $dao_log->infoData($uwl_id);
        if (!empty($info))
        {
            $platform_funds = new \MDAO\Platform_funds_log();
            $curtime = time();
            $platform_funds->addData(array(
                'pfl_type'    => 1,
                'pfl_type_id' => $uwl_id,
                'pfl_amount'  => ($info['uwl_amount'] * -1),
                'pfl_in_time' => $curtime,
                'pfl_reason'  => 'withdraw',
                'pfl_status'  => 2,
                'pfl_last_editor' => parent::$manager_status,
                'pfl_last_edit_time' => $curtime,
            ));
            //$user_funds = new \MDAO\Users_ext_funds();
            //$sql = 'insert into users_ext_funds (u_id, uef_overage) values (' . $info['u_id'] . ', ' . $info['uwl_amount'] . ') ON DUPLICATE KEY update users_ext_funds set uef_overage=uef_overage-' . $info['uwl_amount'] . ' where u_id=' . $info['u_id'];
            //$user_funds->queryData('update users_ext_funds set uef_overage = uef_overage-' . $info['uwl_amount'] . ' where u_id = '. $info['u_id']);
        }
        if (!$is_ajax)
        {
            return 1;
        }
        echo 1;exit;
    }

    public function sendWithdrawMessage()
    {
        $uwl_id = isset($_REQUEST['uwl_id']) && intval($_REQUEST['uwl_id']) > 0 ? intval($_REQUEST['uwl_id']) : 0;
        $is_ajax = isset($_REQUEST['is_ajax']) ? intval($_REQUEST['is_ajax']) : 0;
        if (!$uwl_id)
        {
            if (!$is_ajax)
            {
                return 0;
            }
            echo 0;exit;
        }
        $dao_log = new \MDAO\Log(array('table'=>'user_withdraw_log'));
        $info = $dao_log->infoData($uwl_id);
        if (!empty($info) && isset($info['u_id']) && $info['u_id'] > 0)
        {
            if (isset($info['uwl_status']))
            {
                $message = '尊敬的用户您好，您提交于' . date('Y-m-d H:i:s', $info['uwl_in_time']) . '的提现申请结果：';
                if ($info['uwl_status'] == -1)
                {
                    $message .= '失败';
                }
                elseif ($info['uwl_status'] == 1)
                {
                    $message .= '审核通过，正在处理';
                }
                elseif ($info['uwl_status'] == 2)
                {
                    $message .= '已成功';
                }
                else
                {
                    if (!$is_ajax)
                    {
                        return 0;
                    }
                    echo 0;exit;
                }
                $user_dao = new \MDAO\Users();
                $user_info = $user_dao->infoData($info['u_id']);
                $result = 0;
                if (isset($user_info['u_mobile']) && intval($user_info['u_mobile']) > 0)
                {
                    $result = sendSms($user_info['u_mobile'],$message);
                }
                if (!$result)
                {
                    if (!$is_ajax)
                    {
                        return 0;
                    }
                    echo 0;exit;
                }
                if (!$is_ajax)
                {
                    return 1;
                }
                echo 1;exit;
            }
        }
        if (!$is_ajax)
        {
            return 0;
        }
        echo 0;exit;
    }





}