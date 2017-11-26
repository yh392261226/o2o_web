<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-10-11 15:33:03
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-10-11 15:33:30
 */

namespace WDAO;

class Users_cur_position extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Users_cur_position'));
    }

}