<?php
/**
 * 评价接口
 */
namespace App\Controller;

class Log extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        
    }

     /*获取用户资金日志*/
    public function getUsersFundsLog()
    {
        if(empty($_GET['u_id']) || empty($u_id = intval($_GET['u_id']))){
            $this->exportData( array('msg'=>'用户id不能为空'),0);
        }
        $category = !empty($_GET['category'])  ? trim($_GET['category']) : 'all';
        $dao_platform_funds_log = new \WDAO\Users(array('table'=>'platform_funds_log'));
        $r_data = array();
        $w_data = array();
        /*充值*/
        $recharge_list['data'] = array();
        $withdraw_list['data'] = array();
        if($category=='recharge' || $category=='all'){
        $dao_recharge_log = new \WDAO\Users(array('table'=>'user_recharge_log'));
        $recharge_list = $dao_recharge_log ->listData(array('u_id'=>$u_id,'pager'=>false,'url_status'=>1,'where'=>'p_id != 0','fields'=>'url_amount as amount,url_id as id ,url_in_time as time,url_overage as balances, "recharge"'));
        }
        /*雇主支付工人工资*/

        if($category=='recharge' || $category=='all' || $category=='payorder'){
            /*订单*/
            $dao_orders = new \WDAO\Users(array('table'=>'orders'));
            $payorder_list_o_worker = $dao_orders ->listData(
                array(
                    'o_worker'=>$u_id,
                    'pager'=>false,
                    'o_pay'=>1,
                    'fields'=>'o_id'
                    )
                );
            $o_arr=array();
            foreach ($payorder_list_o_worker['data'] as $value) {
                $o_arr[] = $value['o_id'];
            }
            $o_str = implode(',',$o_arr);

            $payorder_list['data'] = array();
            if(!empty($o_str)){
                /*日志*/
                $payorder_list = $dao_platform_funds_log ->listData(
                    array(

                        'pager'=>false,
                        'pfl_reason'=>'payorder',
                        'where'=>'pfl_type_id IN ('.$o_str.')',
                        'fields'=>'pfl_amount as amount,pfl_id as id ,pfl_in_time as time,pfl_last_editor as balances, "income"',
                    )
                );
            }

            /*订单改价后退换日志*/
            $dao_task = new \WDAO\Users(array('table'=>'tasks'));
            $payorder_list_u_id = $dao_task ->listData(
                array(
                    't_author'=>$u_id,
                    'pager'=>false,
                    'where'=>'t_status > 0',
                    'fields'=>'t_id'
                    )
                );
            $id_arr=array();
            foreach ($payorder_list_u_id['data'] as $value) {
                $id_arr[] = $value['t_id'];
            }
            $o_str = '';
            $o_str = implode(',',$id_arr);

            $retrun_list['data'] = array();
            if(!empty($o_str)){
                /*日志*/
                $retrun_list = $dao_platform_funds_log ->listData(
                    array(
                        'pager'=>false,
                        'pfl_reason'=>'taskreturn',
                        'pfl_type' => 3,
                        'where'=>'pfl_type_id IN ('.$o_str.')',
                        'fields'=>'pfl_amount as amount,pfl_id as id ,pfl_in_time as time,pfl_last_editor as balances,pfl_rate, "taskreturn"',
                    )
                );
            }

            $time = array();
            $arr = array();
            $r_data = array_merge($recharge_list['data'],$payorder_list['data'],$retrun_list['data']);
            foreach ($r_data as $k => $v) {
                $time[$k]  = $v['time'];
                $arr[$k] = $v;
                $r_data[$k]['amount'] = $v['amount'];
            }
            // $res = array_multisort($time, SORT_DESC, $arr, SORT_ASC, $r_data);
        }



        /*提现*/
        if($category=='withdraw' || $category=='all'){
        $dao_withdraw_log = new \WDAO\Users(array('table'=>'user_withdraw_log'));
        $withdraw_list = $dao_withdraw_log ->listData(array('u_id'=>$u_id,'pager'=>false,'where' => 'uwl_status > -1', 'fields'=>'uwl_id as id,uwl_amount as amount,uwl_in_time as time,uwl_overage as balances,"withdraw"'));
        }


        if($category=='withdraw' || $category=='all' || $category=='payorder'){
            /*订单*/
            $dao_orders = new \WDAO\Users(array('table'=>'orders'));
            $payorder_list_u_id = $dao_orders ->listData(
                array(
                    'u_id'=>$u_id,
                    'pager'=>false,
                    'o_pay'=>1,
                    'fields'=>'o_id'
                    )
                );
            $o_arr=array();
            foreach ($payorder_list_u_id['data'] as $value) {
                $o_arr[] = $value['o_id'];
            }
            $o_str = '';
            $o_str = implode(',',$o_arr);

            $payorder_list['data'] = array();
            if(!empty($o_str)){
                /*日志*/
                $payorder_list = $dao_platform_funds_log ->listData(
                    array(

                        'pager'=>false,
                        'pfl_reason'=>'payorder',
                        'where'=>'pfl_type_id IN ('.$o_str.')',
                        'fields'=>'pfl_amount as amount,pfl_id as id ,pfl_in_time as time,pfl_last_editor as balances,pfl_rate, "pay"',
                    )
                );
            }
            $time = array();
            $arr = array();
            $w_data = array_merge($withdraw_list['data'],$payorder_list['data']);

            foreach ($w_data as $k => $v) {
                /*订单收入金额修改*/
                if(isset($v['pfl_rate']) && floatval($v['pfl_rate']) > 0){

                    $w_data[$k]['amount'] = $v['amount']/(1-floatval($v['pfl_rate']));
                    unset($w_data[$k]['pfl_rate']);

                }
                $time[$k]  = $v['time'];
                $arr[$k] = $v;
            }

            // $res = array_multisort($time, SORT_DESC, $arr, SORT_ASC, $w_data);
        }

/*支出收入订单合并*/
        $time = array();
        $arr = array();
        $data = array();
        $data = array_merge($w_data,$r_data);
        foreach ($data as $k => $v) {
            $time[$k]  = $v['time'];
            $arr[$k] = $v;
        }
        $res = array_multisort($time, SORT_DESC, $arr, SORT_ASC, $data);

        foreach ($data as $key => &$value) {
            /*类型*/
            if(isset($value['withdraw'])){
                $value['type'] = 'withdraw';
                unset($value['withdraw']);

            }elseif(isset($value['recharge'])){
                $value['type'] = 'recharge';
                unset($value['recharge']);
            }elseif(isset($value['pay'])){
                $value['type'] = 'pay';
                unset($value['pay']);
            }elseif(isset($value['income'])){
                $value['type'] = 'income';
                unset($value['income']);
            }elseif(isset($value['taskreturn'])){
                $value['type'] = 'income';
                unset($value['taskreturn']);
            }else{

            }

            if($value['amount'] < 0){
                $value['amount'] = $value['amount'] * -1;
            }

            $value['amount'] = number_format($value['amount'], 2, '.', '');

        }
        $this->exportData( array('data'=>$data),1);

    }

     /*充值开始*/
        /*充值记录接口*/
        public function applyRechargeLog()
    {
        if(empty($_REQUEST['u_id']) || empty($u_id = intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_REQUEST['url_amount']) || floatval($_REQUEST['url_amount']) <= 0){
            $this->exportData( array('msg'=>'充值金额必须大于0元'),0);
        }else{
            $url_amount = floatval($_REQUEST['url_amount']);
        }
        if(empty($_REQUEST['p_id']) || empty($p_id = intval($_REQUEST['p_id']))){
            $this->exportData( array('msg'=>'支付方式不能为空'),0);
        }

        /*设置充值额度限制*/
        if(isset($this ->web_config['recharge_amount_min']) && $this ->web_config['recharge_amount_min'] > 0)
        {
            if($this ->web_config['recharge_amount_min'] > $url_amount)
            {
                $this->exportData( array('msg'=>'充值失败!最小充值金额为'.$this ->web_config['recharge_amount_min'].'元;'),0);
            }
        }
        if(isset($this ->web_config['recharge_amount_max']) && $this ->web_config['recharge_amount_max'] > 0)
        {
            if($this ->web_config['recharge_amount_max'] < $url_amount)
            {
                $this->exportData( array('msg'=>'充值失败!最大充值金额为'.$this ->web_config['recharge_amount_max'].'元;'),0);
            }
        }

        /*用户充值申请日志*/
        $RC_log_id = $this ->usersRechargeLog($u_id, floatval($url_amount), $name = '', $card = '', $status = 0, $p_id);
        if (!empty(intval($RC_log_id))) {
            /*第三方充值*/
            $dao_users = new \WDAO\Users(array('table'=>'users'));
            if ($p_id = $this ->web_config['wx_pid'])/*微信支付*/
            {
                $res = $dao_users ->WXRecharge($RC_log_id,floatval($url_amount));
                if($res){
                    $this->exportData( $res,1);
                }else{
                    $this->exportData( array('msg'=>'充值失败!系统错误请联系管理员'),0);
                }
            }
            elseif($p_id = $this ->web_config['al_pid'])/*支付宝支付*/
            {

            }else{
                $this->exportData( array('msg'=>'充值失败!系统错误请联系管理员'),0);
            }
        }else{
            $this->exportData( array('msg'=>'充值失败!系统错误请联系管理员'),0);
        }
    }

    /*用户提现申请接口*/
    public function applyWithdraw()
    {
        if(empty($_REQUEST['u_id']) || empty($u_id = intval($_REQUEST['u_id']))){
            $this->exportData( array('msg'=>'用户ID为空'),0);
        }
        if(empty($_REQUEST['uwl_amount']) || empty($uwl_amount = floatval($_REQUEST['uwl_amount']))){
            $this->exportData( array('msg'=>'提现金额不能为空'),0);
        }
        if(empty($_REQUEST['p_id']) || empty($p_id = intval($_REQUEST['p_id']))){
            $this->exportData( array('msg'=>'提现方式不能为空'),0);
        }
        if(empty($_REQUEST['uwl_card']) || empty($uwl_card = intval($_REQUEST['uwl_card']))){
            $this->exportData( array('msg'=>'提现账号不能为空'),0);
        }
        if(empty($_REQUEST['uwl_truename']) || empty($uwl_truename = trim($_REQUEST['uwl_truename']))){
            $this->exportData( array('msg'=>'提现账号姓名不能为空'),0);
        }
        if(empty($_REQUEST['u_pass']) || empty($u_pass = trim($_REQUEST['u_pass']))){
            $this->exportData( array('msg'=>'提现密码不能为空'),0);
        }

        /*设置充值额度限制*/
        if(isset($this ->web_config['withdraw_amount_min']) && $this ->web_config['withdraw_amount_min'] > 0)
        {
            if($this ->web_config['withdraw_amount_min'] > $uwl_amount)
            {
                $this->exportData( array('msg'=>'提现失败!最小提现金额为'.$this ->web_config['withdraw_amount_min'].'元;'),0);
            }
        }
        if(isset($this ->web_config['withdraw_amount_max']) && $this ->web_config['withdraw_amount_max'] > 0)
        {
            if($this ->web_config['withdraw_amount_max'] < $uwl_amount)
            {
                $this->exportData( array('msg'=>'提现失败!最大提现金额为'.$this ->web_config['withdraw_amount_max'].'元;'),0);
            }
        }


        /*验证支付密码是否正确*/
        $dao_users = new \WDAO\Users(array('table'=>'users'));
        $check_res = $dao_users ->checkUserPayPassword(array('u_id' =>$u_id,'u_pass' =>$u_pass));
        if(!$check_res){
            $this->exportData( array('msg'=>'提现密码错误'),0);
        }
        $dao_user_withdraw_log = new \WDAO\Users(array('table'=>'user_withdraw_log'));
        $dao_users_ext_funds = new \WDAO\Users(array('table'=>'users_ext_funds'));
        /*判断用户余额是否大于提现余额*/
        $uef_overage = $dao_users_ext_funds ->infoData(array('fields'=>'u_id,uef_overage','key'=>'u_id','val' => $u_id,'pager'=>false));

        if(!empty($uef_overage['uef_overage'])){
            if(intval($uef_overage['uef_overage']) >= $uwl_amount){
                /*处理提现过程start*/

                /*用户余额修改*/
                $funds_res = $this ->userFunds($u_id, $uwl_amount*-1, $type = 'withdraw');
                if(!$funds_res){
                    $this->exportData( array('msg'=>'提现申请失败,请联系管理员'),0);
                }
                /*用户提现申请日志*/
                $WD_log_id = $this ->usersWithdrawLog($u_id, floatval($uwl_amount), $uwl_truename, $uwl_card, $status = 0, $p_id);
                /*平台资金流向修改*/
                if(intval($WD_log_id <=0 )){
                    $this->exportData( array('msg'=>'提现申请失败,请联系管理员'),0);
                }
                $dao_platform_funds_log = new \WDAO\Users(array('table'=>'platform_funds_log'));
                $data = array();
                $data['pfl_type'] = 1;
                $data['pfl_type_id'] = $WD_log_id;
                $data['pfl_amount'] = $uwl_amount;
                $data['pfl_in_time'] = time();
                $data['pfl_reason'] = 'withdraw';
                $data['pfl_status'] = 1;
                $dao_platform_funds_log -> addData($data);
                $this->exportData( array('msg'=>'提现申请添加成功'),1);

                /*处理提现过程end*/
            }else{
                $this->exportData( array('msg'=>'用户余额不足'),0);
            }
        }else{
            $this->exportData( array('msg'=>'用户余额不足'),0);
        }
    }
       

}