<?php
namespace MDAO;

class Managers_privileges_group extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'managers_privileges_group'));
    }

    public function listDataAll()
    {
        return $this->listData(array('pager' => 0));
    }
}