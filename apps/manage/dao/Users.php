<?php
namespace MDAO;

class Users extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Users'));
    }


}