<?php
namespace App\Controller;
class Tools extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $data = array();
        $data['datetime'] = $this->serverTime();
        $this->exportData($data);
    }

    private function serverTime()
    {
        return array(
            'timestamp' => time(),
            'date'      => date('Y-m-d'), //2009-10-10
            'his'       => date('H:i:s'), //22:10:10
        );
    }
}