<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-11-09 17:59:33
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-11-09 18:00:52
 */
namespace MDAO;

class User_recharge_log extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'user_recharge_log'));
    }
}