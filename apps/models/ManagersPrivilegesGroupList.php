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
class ManagersPrivilegesGroupList extends Swoole\Model
{
	public $table = 'managers_privileges_group_list';
    public $primary = "mpg_id";
}