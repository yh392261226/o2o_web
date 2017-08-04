<?php
namespace App\Model;
use Swoole;

class Managers extends Swoole\Model
{
    public $table = 'managers';
    public $primary = "m_id";
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
    public function findAll($where = '',$field = '',$first_page = '',$last_page = '')
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
	// public function getAll($where = '',$field = '',$first_page = '',$last_page = '')
	// {
   		// // var_dump($where);exit;
   		// $this->check_value($where);
   		// $params=[
		    // 'where'=>$this->where,         //$where为字符串或者一维关联数组
		    // 'orwhere'=>$orwhere,     //$orwhere为字符串或者一维关联数组, 注意：如果写成字符串，框架会把$orwhere当成一个方法来调用（这个地方这种处理不知道是作者有意为之还是手误。。）
		    // 'limit'=>"$this->first_page, $this->last_page",         //$limit为整数或者字符串
		    // 'order'=>$order,         //$order为字符串，如'id DESC'
		    // 'group'=>$group,         //$group为字符串
		    // 'having'=>$having,       //$having为字符串

		    // 'walk'=>[
		    //     ['k'=>'v'],       //v为字符串或者数组，函数k必须存在
		    //     ['like'=>[$field, $like]],
		    //     ['in'=>[$field, $ins]],
		    //     ['notin'=>[$field, $ins]],
		    //     ['join'=>[$table, $on]],
		    //     ['leftjoin'=>[$table, $on]],
		    //     ['rightjoin'=>[$table, $on]],
		    // ],
			// 'page'=>$this->first_page,
			// 'pagesize'=>$this->last_page,
		    // 'page'=>n,     //表示需要分页，并且取第n页的数据，这个时候$pager被初始化为一个pager对象实例
		    // 'pagesize'=>m, //分页时，设置每一页取m条记录，默认为10
		// ];
		// $pager = null; 
		// $result['list'] = $this->gets($params, $pager);
		//$total_num表示符合条件的总的记录条数
		// $result['page']['total_num'] = $pager->total;
		// //$total_page表示总共有几页数据
		// $result['page']['total_page'] = (int)$pager->totalpage;
		// //$current_page表示当前取的是第几页的数据
		// $result['page']['current_page'] = $pager->page;
		// $result['page'] = $pager->render();
		// return $result;

   	// }
   	// public function getAll($where = '',$field = '',$first_page = '',$last_page = '')
   	// {
   		// var_dump($where);exit;
   		// $this->check_value($where);
   		// $params=[
		    // 'where'=>$this->where,         //$where为字符串或者一维关联数组
		    // 'orwhere'=>$orwhere,     //$orwhere为字符串或者一维关联数组, 注意：如果写成字符串，框架会把$orwhere当成一个方法来调用（这个地方这种处理不知道是作者有意为之还是手误。。）
		    // 'limit'=>"$this->first_page, $this->last_page",         //$limit为整数或者字符串
		    // 'order'=>$order,         //$order为字符串，如'id DESC'
		    // 'group'=>$group,         //$group为字符串
		    // 'having'=>$having,       //$having为字符串

		    // 'walk'=>[
		    //     ['k'=>'v'],       //v为字符串或者数组，函数k必须存在
		    //     ['like'=>[$field, $like]],
		    //     ['in'=>[$field, $ins]],
		    //     ['notin'=>[$field, $ins]],
		    //     ['join'=>[$table, $on]],
		    //     ['leftjoin'=>[$table, $on]],
		    //     ['rightjoin'=>[$table, $on]],
		    // ],
			// 'page'=>$this->first_page,
			// 'pagesize'=>$this->last_page,
		    // 'page'=>n,     //表示需要分页，并且取第n页的数据，这个时候$pager被初始化为一个pager对象实例
		    // 'pagesize'=>m, //分页时，设置每一页取m条记录，默认为10
		// ];
		// // $pager = null; 
		// $result['list'] = $this->getList($params);

		// // var_dump($result['list']);exit;
		// $array['page'] = Swoole::$php->env['page'];
		// $array['start'] = Swoole::$php->env['start'];
		// $array['end'] = Swoole::$php->env['end'];
		// $array['perpage'] = Swoole::$php->env['pages'];
		// $array['pagesize'] = Swoole::$php->env['pagesize'];
		// $array['total'] = Swoole::$php->env['num'];
		// $pager = new \Swoole\Pager($array);
		// //$total_num表示符合条件的总的记录条数
		// // $result['page']['total_num'] = $pager->total;
		// // //$total_page表示总共有几页数据
		// // $result['page']['total_page'] = (int)$pager->totalpage;
		// // //$current_page表示当前取的是第几页的数据
		// // $result['page']['current_page'] = $pager->page;
		// $result['page'] = $pager->render();
		// var_dump($result['page']);
		// return $result;

   	// }
    public function findOne($where = '',$field = '')
    {
    	$this->check_value($where,$field);
    	return $this->select($this->field)->where($this->where)->fetch();
    }
    public function check_value($where = '',$field = '',$first_page = '',$last_page = '')
    {
    	$this->where = !empty($where) ? $where : $this->where;
    	$this->field = !empty($field) ? $field : $this->field;
    	$this->first_page = !empty($first_page) ? $first_page : $this->first_page;
    	$this->last_page = !empty($last_page) ? $last_page : $this->last_page;
    }
}