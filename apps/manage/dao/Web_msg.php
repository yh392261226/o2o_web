<?php
namespace MDAO;

class Web_msg extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Web_msg'));
    }
}