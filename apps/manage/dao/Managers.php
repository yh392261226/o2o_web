<?php
namespace MDAO;

class Managers extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Managers'));
    }

    public function checkManagerName($name)
    {
        if ('' != trim($name))
        {
            $counts = $this->countData(array('m_name' => $name));
            if ($counts > 0)
            {
                return true; //exusts manager
            }
        }
        return false; //does not exists manager
    }

}