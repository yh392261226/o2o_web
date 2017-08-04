<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-01 16:32:28
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-04 09:44:19
 */

namespace App\AdminBase;
use Swoole;

class AdminBase extends Swoole\Controller
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->session->start();
        $_SESSION['manager_id'] = 0;
    }
    protected function enctypePass($password  = '')
    {
        return md5($password);
    }
}