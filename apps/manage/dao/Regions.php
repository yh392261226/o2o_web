<?php
namespace MDAO;

class Regions extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Regions'));
    }

    public function checkRegionName($name)
    {
        if (!empty($name))
        {
            $counts = $this->countData($name);
            if ($counts > 0)
            {
                return true; //exusts manager
            }
        }
        return false; //does not exists manager
    }

}