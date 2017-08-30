<?php
namespace MDAO;

class Articles extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Managers'));
    }
}
