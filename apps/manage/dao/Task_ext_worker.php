<?php
namespace MDAO;

class Task_ext_worker extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Task_ext_worker'));
    }
}