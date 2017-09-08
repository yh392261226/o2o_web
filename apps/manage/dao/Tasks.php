<?php
namespace MDAO;

class Tasks extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'tasks'));
    }
}