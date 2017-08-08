<?php
namespace DAO;

/**
 * Class UsManagerer
 * example: $user = new App\DAO\Manager();  $user->get();
 * @package App\DAO
 */
class Manager
{
    public function show_index($first_page,$m_name,$last_page = 2,$filed = '*')
    {
        $first_page = !empty($first_page) ? $first_page : 1;
        $m_name= !empty($m_name) ? $m_name : false;

        if ($m_name) {
            $where["m_name"] = $m_name;
        }else{
            $where = '';
        }
		$manager = model('Managers');
    	$ret_info['list'] = $manager->findAll($where,$filed,$first_page,$last_page);
    	if (!empty($ret_info['list']))
        {
            $ret_info['page'] = $manager->page();
        }
        else
        {
            $ret_info['page'] = array();
        }
        // var_dump($where);
        // $ret_info = $manager->getAll($where); 
        // var_dump($ret_info)       ;exit();
        return $ret_info;
    }
    public function edit_manager_info()
    {
        # code...
    }
}
