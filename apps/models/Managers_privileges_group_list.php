<?php
namespace App\Model;
use Swoole;

class Managers_privileges_group_list extends Swoole\Model
{
	public $table = 'managers_privileges_group_list';
    public $primary = "mpg_id";
	protected $where = "1";
	protected $field = '*';
	protected $first_page = 0;
	protected $last_page = 30;
    public function save($id,$data,$where = '')
    {
    	$this->check_value($where);
    	if ($this->where == '1') {
    		$where[$this->primary] = $id;
    	}
    	$this->update($id, $data, $this->table, $this->where);
    }
    public function findAll($where = '',$field = '',$first_page,$last_page)
    {
    	$this->check_value($where,$field,$first_page,$last_page);
    	return $this->select($this->field)
			    	->where($this->where)
    				->paginate($this->first_page, $this->last_page)
			    	->fetchall();
    }
    public function page()
    {
    	$pager = $this->select($this->field)
			->where($this->where)
    		->paginate($this->first_page, $this->last_page)
    		->getPager();

        $pager->disable('first');
        return $pager->render();
    }
    public function findOne($where = '',$field = '')
    {
    	$this->check_value($where,$field);
    	return $this->select($this->field)->where($this->where)->fetch();
    }
    public function check_value($where,$field,$first_page,$last_page)
    {
    	$this->where = !empty($where) ? $where : $this->where;
    	$this->field = !empty($field) ? $field : $this->field;
    	$this->first_page = !empty($first_page) ? $first_page : $this->first_page;
    	$this->last_page = !empty($last_page) ? $last_page : $this->last_page;
    }
}