<?php
namespace App\Controller;

class Users extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->users_dao = new \MDAO\Users();
        $this->users_ext_info_dao = new \MDAO\Users_ext_info();
        $this->users_ext_funds_dao = new \MDAO\Users_ext_funds();
        $this->user_recharge_log_dao = new \MDAO\User_recharge_log();
        //$this->db->debug = 1;
    }

    /**
     * ****[ users ]***********************************************************************************************
     */

    public function add()
    {
        if (isset($_REQUEST['u_mobile']) && '' != trim($_REQUEST['u_mobile']))
        {
            $data = $info_data = $postion_data = array();
            $data['u_mobile'] = isset($_REQUEST['u_mobile']) ? trim($_REQUEST['u_mobile']) : '';
            if ('' == $data['u_mobile']) msg('手机号不能为空', 0);
            if ($this->checkMobile($data['u_mobile']))
            {
                msg('手机号已存在', 0);
            }
            $data['u_bind_mobile'] = isset($_REQUEST['u_bind_mobile']) ? trim($_REQUEST['u_bind_mobile']) : 0;
            $data['u_phone'] = isset($_REQUEST['u_phone']) ? trim($_REQUEST['u_phone']) : 0;
            $data['u_token'] = isset($_REQUEST['u_token']) ? trim($_REQUEST['u_token']) : $data['u_mobile'];
            $data['u_name'] = isset($_REQUEST['u_name']) ? trim($_REQUEST['u_name']) : $data['u_mobile'];
            $data['u_pass'] = isset($_REQUEST['u_pass']) ? encyptPassword(trim($_REQUEST['u_pass'])) : encyptPassword('123456');
            $data['u_sex'] = isset($_REQUEST['u_sex']) ? intval($_REQUEST['u_sex']) : -1;
            $data['u_skills'] = isset($_REQUEST['u_skills']) ? trim($_REQUEST['u_skills']) : 0;
            $data['u_true_name'] = isset($_REQUEST['u_true_name']) ? trim($_REQUEST['u_true_name']) : '';
            $data['u_idcard'] = isset($_REQUEST['u_idcard']) ? trim($_REQUEST['u_idcard']) : '';
            $data['u_status'] = 0;
            $data['u_online'] = 1;
            $data['u_in_time'] = $data['u_in_time'] = time();

            $info_data['uei_province'] = isset($_REQUEST['uei_province']) ? intval($_REQUEST['uei_province']) : 12;
            $info_data['uei_city'] = isset($_REQUEST['uei_city']) ? intval($_REQUEST['uei_city']) : 167;
            $info_data['uei_area'] = isset($_REQUEST['uei_area']) ? intval($_REQUEST['uei_area']) : 0;
            $info_data['uei_address'] = isset($_REQUEST['uei_address']) ? trim($_REQUEST['uei_address']) : '';
            $info_data['uei_zip'] = isset($_REQUEST['uei_zip']) ? trim($_REQUEST['uei_zip']) : '000000';
            $info_data['uei_info'] = isset($_REQUEST['uei_info']) ? trim($_REQUEST['uei_info']) : '';

            $postion_data['ucp_posit_x'] = isset($_REQUEST['ucp_posit_x']) ? floatval($_REQUEST['ucp_posit_x']) : 0.00000000;
            $postion_data['ucp_posit_y'] = isset($_REQUEST['ucp_posit_y']) ? floatval($_REQUEST['ucp_posit_y']) : 0.00000000;

            if (!empty($data))
            {
                \Swoole::$php->db->start();
                $u_id = $this->users_dao->addData($data);
                if ($u_id > 0)
                {
                    $info_data['u_id'] = $u_id;
                    $info = $this->users_ext_info_dao->addData($info_data);
                    $funds = $this->users_ext_funds_dao->addData(array('u_id' => $u_id));
                    $postion = model('Users_cur_position')->addData(array(
                        'u_id' => $u_id,
                        'ucp_posit_x' => $postion_data['ucp_posit_x'],
                        'ucp_posit_y' => $postion_data['ucp_posit_y'],
                        'ucp_last_edit_time' => time(),
                    ));
                    if ($u_id && $info && $funds && $postion)
                    {
                        \Swoole::$php->db->commit();
                        msg('操作成功', 1, '/Users/list');
                    }
                }
                \Swoole::$php->db->rollback();
            }
            msg('操作失败', 0, '/Users/list');
        }

//        $skills_dao = new \MDAO\Skills(array('table' => 'skills'));
//        $skillslist = $skills_dao->listData(array(
//            'pager' => 0,
//            's_status' => 1,
//        ));
//        $this->tpl->assign('skillslist', $skillslist);
        $this->mydisplay();
    }

    public function del()
    {
//        $this->db->debug = 1;
        $result = 0;
        if (isset($_REQUEST['u_id']))
        {
            if (is_array($_REQUEST['u_id']) || strpos($_REQUEST['u_id'], ','))
            {
                $result = $this->users_dao->delUser(array('u_id' => array('type' => 'in', 'value' => $_REQUEST['u_id'])));
            }
            else
            {
                $result = $this->users_dao->delUser(intval($_REQUEST['u_id']));
            }
        }
        if (!$result) {
            //FAILED
            msg('操作失败', 0);
        }

        //删除成功删掉其他与该用户相关表的信息
        $this->users_ext_funds_dao->delData(intval($_REQUEST['u_id'])); //钱
        $this->users_ext_info_dao->delData(intval($_REQUEST['u_id'])); //其他信息
        model('Users_cur_position')->delData(intval($_REQUEST['u_id'])); //位置信息
        //SUCCESSFUL
        msg('操作成功', 1, '/Users/list');
    }

    public function info()
    {
        $info = array();
        if (isset($_REQUEST['u_id']) || isset($_REQUEST['key']))
        {
            if (isset($_REQUEST['u_id']))
            {
                $info = $this->users_dao->infoData(intval($_REQUEST['u_id']));
            }
            elseif (isset($_REQUEST['key']))
            {
                $info = $this->users_dao->infoData(array('key' => trim($_REQUEST['key']), 'val' =>  $_REQUEST['val']));
            }
        }

        if (!empty($info))
        {
            $funds = $this->users_ext_funds_dao->infoData(intval($_REQUEST['u_id']));
            if (!empty($funds))
            {
                $info['funds'] = $funds;
            }

            $ext_info = $this->users_ext_info_dao->infoData(intval($_REQUEST['u_id']));
            if (!empty($ext_info))
            {
                $info['ext'] = $ext_info;
            }
            unset($funds, $ext_info);
        }
//print_r($info);
        $this->tpl->assign('info', $info);
        $this->mydisplay();
    }

    public function list()
    {
        $list = $data = array();
        if (isset($_REQUEST['u_id'])) $data['u_id'] = array('type' => 'in', value => $_REQUEST['u_id']);
        if (isset($_REQUEST['u_name'])) $data['u_name'] = array('type'=>'like', 'value' => trim($_REQUEST['u_name']));
        if (isset($_REQUEST['u_mobile'])) $data['u_mobile'] = intval($_REQUEST['u_mobile']);
        if (isset($_REQUEST['u_bind_mobile'])) $data['u_bind_mobile'] = intval($_REQUEST['u_bind_mobile']);
        if (isset($_REQUEST['u_phone'])) $data['u_phone'] = trim($_REQUEST['u_phone']);
        if (isset($_REQUEST['u_fax'])) $data['u_fax'] = trim($_REQUEST['u_fax']);
        if (isset($_REQUEST['u_sex'])) $data['u_sex'] = trim($_REQUEST['u_sex']);
        if (isset($_REQUEST['u_online'])) $data['u_online'] = intval($_REQUEST['u_online']);
        if (isset($_REQUEST['u_status'])) $data['u_status'] = intval($_REQUEST['u_status']);
        if (isset($_REQUEST['u_type'])) $data['u_type'] = intval($_REQUEST['u_type']);
        if (isset($_REQUEST['u_task_status'])) $data['u_task_status'] = intval($_REQUEST['u_task_status']);
        if (isset($_REQUEST['u_start'])) $data['u_start'] = intval($_REQUEST['u_start']);
        if (isset($_REQUEST['u_top'])) $data['u_top'] = intval($_REQUEST['u_top']);
        if (isset($_REQUEST['u_recommend'])) $data['u_recommend'] = intval($_REQUEST['u_recommend']);
        if (isset($_REQUEST['u_true_name'])) $data['u_true_name'] = trim($_REQUEST['u_true_name']);
        if (isset($_REQUEST['u_idcard'])) $data['u_idcard'] = trim($_REQUEST['u_idcard']);
        $data['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        //可以区间值
        if (isset($_REQUEST['u_in_time'])) $data['u_in_time'] = strtotime(trim($_REQUEST['u_in_time']));
        if (isset($_REQUEST['u_last_edit_time'])) $data['u_last_edit_time'] = strtotime(trim($_REQUEST['u_last_edit_time']));
        if (isset($_REQUEST['u_credit'])) $data['u_credit'] = intval($_REQUEST['u_credit']);
        if (isset($_REQUEST['u_jobs_num'])) $data['u_jobs_num'] = intval($_REQUEST['u_jobs_num']);
        if (isset($_REQUEST['u_worked_num'])) $data['u_worked_num'] = intval($_REQUEST['u_worked_num']);
        if (isset($_REQUEST['u_high_opinions'])) $data['u_high_opinions'] = intval($_REQUEST['u_high_opinions']);
        if (isset($_REQUEST['u_low_opinions'])) $data['u_low_opinions'] = intval($_REQUEST['u_low_opinions']);
        if (isset($_REQUEST['u_middle_opinions'])) $data['u_middle_opinions'] = intval($_REQUEST['u_middle_opinions']);
        if (isset($_REQUEST['u_dissensions'])) $data['u_dissensions'] = intval($_REQUEST['u_dissensions']);

        $list = $this->users_dao->listData($data);//print_r($list);exit;
        $this->tpl->assign('list', $list);
        $this->myPager($list['pager']);
        $this->mydisplay();
    }

    public function sendMessage()
    {
        $data = array();
        if (isset($_REQUEST['u_id'])) $data['u_id'] = intval($_REQUEST['u_id']);
        if (isset($data['u_id']) && 0 < $data['u_id'])
        {
            if (isset($_REQUEST['content'])) $data['content'] = trim($_REQUEST['content']);
            if (isset($_REQUEST['u_mobile'])) $data['u_mobile'] = trim($_REQUEST['u_mobile']);
            if (!isset($data['content']) || '' == trim($_REQUEST['content']) || !isset($data['u_mobile']) || 13000000000 > intval($_REQUEST['u_mobile']))
            {
                echo 0;exit;
            }
            $result = sendSms(intval($_REQUEST['u_mobile']), trim($data['content']));
            if ($result)
            {
                echo 1;exit;
            }

        }
        echo 0;exit;
    }

    /*ajax 修改用户余额*/
    public function userFundsEdit()
    {
        if( isset($_REQUEST['u_id']) && !empty($u_id = intval($_REQUEST['u_id'])) && isset($_REQUEST['uef_overage']) && !empty($uef_overage = floatval($_REQUEST['uef_overage'])))
        {
            $log_data = array();
            $funds_data = $this->users_ext_funds_dao->infoData($u_id);

            //记录用户资金变动日志
            $log_data = array(
                'url_amount'       => $uef_overage,
                'u_id'             => $u_id,
                'url_overage'      => isset($funds_data['uef_overage']) ? $funds_data['uef_overage'] : 0,
                'url_in_time'      => time(),
                'url_status'       => 1,
                'url_remark'       => 'manageredit',
                'url_solut_author' => parent::$manager_status,
            );
            $this->user_recharge_log_dao->addData($log_data);

            $result = $this->users_ext_funds_dao->queryData('insert into users_ext_funds values (' . $u_id . ', ' . $uef_overage . ', 0, 0) on duplicate key update uef_overage = ' . $uef_overage);
            if ($result) {
                echo 1;
                return false;
            } else {
                echo -3;/*金额修改失败*/
                return false;
            }
        }
        echo 0;
        return false;
    }

    /*ajax 修改用户工作状态*/
    public function userTaskStatusEdit()
    {
        if( isset($_REQUEST['u_id']) && !empty($u_id = intval($_REQUEST['u_id'])))
        {
            $result = $this->users_dao->updateData(array('u_task_status' => 0),array('u_id' => $u_id));
            if ($result) {
                echo 1;
                return false;
            } else {
                echo -3;/*修改失败*/
                return false;
            }
        }
        echo 0;
        return false;
    }


    private function checkMobile($mobile)
    {
        if ('' != trim($mobile))
        {
            $count = $this->users_dao->countData(array('u_mobile' => $mobile));
            if ($count)
            {
                return true;
            }
        }
        return false;
    }

}