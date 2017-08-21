<?php
namespace App\Controller;

class Index extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->managers_dao = new \MDAO\Managers();
    }
    
    public function test()
    {
        $data = array('pager' => true, 'pagesize' => 1);
        $result = $this->managers_dao->listManagers($data);
        print_r($result);
        echo 'test';
    }
}