<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-10-11 15:31:55
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-10-11 15:32:22
 */
namespace WDAO;

class Users_ext_info extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Users_ext_info'));
    }

}