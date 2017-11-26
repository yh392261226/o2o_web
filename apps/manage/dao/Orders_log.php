<?php
namespace MDAO;

class Orders_log extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Orders_log'));
    }
}