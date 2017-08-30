<?php
namespace App\Model;
use Swoole;

/**
 * 权限模块model
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-08-14
 * @return [type]            [description]
 */
class ManagersPrivilegesGroup extends \CLASSES\ModelBase
{
	public $table = 'managers_privileges_group';
    public $primary = "mpg_id";
}