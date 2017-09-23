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
        );

        if (!empty($api_list))
        {
            foreach ($api_list as $key => $val)
            {
                echo "<a target='_blank' href='" . $api_url . ucfirst($val) . "/index'>" . ucfirst($val) . '</a><br >';
            }
        }
    }
}