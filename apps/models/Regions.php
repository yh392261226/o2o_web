<?php 
namespace App\Model;
class Regions extends \MMODEL\ModelBase
{
    public $table = 'regions';
    public $primary = "r_id";
    private $allow_delete = false;

    public function delData($data = array())
    {
        if ($this->allow_delete == false)
        {
            return false;
        }
    }
}