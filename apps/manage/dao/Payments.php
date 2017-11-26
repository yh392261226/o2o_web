<?php
namespace MDAO;

class Payments extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Payments'));
    }


    public function checkDefault($type, $pid = 0)
    {
        if (intval($type) >= 0)
        {
            $data = array(
                'p_default' => 1,
                'p_type'   => $type,
                'p_id'     => array(
                    'type' => 'notin',
                    'value' => $pid
                ),
            );
            $counts = $this->countData($data);
            if ($counts)
            {
                return true;
            }
        }
        return false;
    }
}