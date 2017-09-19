<?php
namespace WDAO;

class Payments extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Payments'));
    }

}