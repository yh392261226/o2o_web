<?php
namespace MDAO;

class Orders extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Orders'));
    }

    public function updateStatus($data = array(), $param = array())
    {
        if (!empty($data) && !empty($param))
        {
            return $this->updateData($data, $param);
        }
        return false;
    }
}