<?php
namespace WDAO;

class Orders extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Orders'));
    }

}