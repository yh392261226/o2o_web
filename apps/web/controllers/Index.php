<?php
namespace App\Controller;
class Index extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $api_url = 'http://api.gangjianwang.com/';
        $api_list = array(
            'Regions',
            'payments',
            'skills',
            'Users',
            'Advertising',
            'articles',
            'Tasks',
            'Bouns',
            'Tools',
            'Application_config',
            'Orders',
        );

        if (!empty($api_list))
        {
            foreach ($api_list as $key => $val)
            {
                echo "<a target='_blank' href='" . $api_url . ucfirst($val) . "/index'>" . ucfirst($val) . '</a><br >';
            }
        }
    }

    public function sms()
    {
        //$phone = array('18846449055', '13163675676');
        $phone = '13163675676';
        $content = '用户您好，你看到一个美女没';
        $message = '';
        $result = sendSms($phone, $content);
        if (!$result)
        {
            $message = '发送失败';
        }
        else
        {
            $message = '发送成功';
        }
        echo $message;exit;
    }

    public function task()
    {
        echo "<pre>";
        print_r($_REQUEST);
    }

    public function msgtest()
    {
        //$this->db->debug = 1;
        //站内信通知
        $this->msgToUser(array(
            'author' => 0,
            'type'   => 1,
            'status' => 1,
            'to_uid' => 198,
            'title'  => '【测试】',
            'desc'   => '这是一个测试' . time(),
        ));
    }
}