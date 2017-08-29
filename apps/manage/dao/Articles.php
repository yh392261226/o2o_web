<?php
namespace MDAO;

class Articles extends \MDAOBASE\DaoBase
{
	public $articles     = null;
	public $articles_ext = null;

	public function __construct()
    {
		$this->articles     = model("Articles");
		$this->articles_ext = model("Articles_ext");
	}

	/**
	 * list managers by params
	 * @param array|string $data
	 * @return array
	 */
	public function listArticles($data = array())
    {
		$para             = array();
		$para['pager']    = isset($data['pager'])?$data['pager']:true;
		$para['page']     = isset($data['page'])?$data['page']:1;
		$para['pagesize'] = isset($data['pagesize'])?$data['pagesize']:PAGESIZE;
		if (!empty($data))
		{
			$para['a_id']        = isset($data['a_id'])?intval($data['a_id']):'';
			$para['ac_id']       = isset($data['ac_id'])?trim($data['ac_id']):'';
			$a_title             = isset($data['a_title'])?trim($data['a_title']):'';
			$a_info              = isset($data['a_info'])?trim($data['a_info']):'';
			$a_link              = isset($data['a_link'])?trim($data['a_link']):'';
			$para['a_author']    = isset($data['a_author'])?trim($data['a_author']):'';
			$para['a_top']       = isset($data['a_top'])?trim($data['a_top']):'';
			$para['a_recommend'] = isset($data['a_recommend'])?trim($data['a_recommend']):'';
			$para['a_status']    = isset($data['a_status'])?trim($data['a_status']):'';
			$para['r_id']        = isset($data['r_id'])?trim($data['r_id']):'';
			$start_time          = isset($data['start_time'])?$data['start_time']:'';
			$end_time            = isset($data['end_time'])?$data['end_time']:'';
			$para['where']       = ' 1 ';
			if (isset($data['where']))
			{
				$para['where'] .= ' AND '.$data['where'];
			}

			if ('' != $start_time)
			{
				$para['where'] .= ' AND a_start_time >='.$start_time;
			}

			if ('' != $end_time)
			{
				$para['where'] .= ' AND a_start_time <= '.$end_time;
			}

			if ('' != $a_title)
			{
				$para['where'] .= ' AND a_title LIKE "%'.$a_title.'%"';
			}

			if ('' != $a_info)
			{
				$para['where'] .= ' AND a_info LIKE "%'.$a_info.'%"';
			}

			if ('' != $a_link)
			{
				$para['where'] .= ' AND a_link LIKE "%'.$a_link.'%"';
			}

			$para['page']     = isset($data['page'])?$data['page']:1;
			$para['pager']    = isset($data['pager'])?$data['pager']:true;
			$para['pagesize'] = isset($data['pagesize'])?$data['pagesize']:PAGESIZE;

		}
		//print_r($para);exit;
		$para = @array_filter($para, 'strlen');//remove the false value of the array
		return $this->articles->getDatas($para);
	}

	/**
	 * info manager by params
	 * @param unknown $data
	 * @return unknown|array
	 */
	public function infoArticles($data = array())
    {
		if (!empty($data))
		{
			return $this->articles->getDatas($data);
		}
		return array();
	}

	/**
	 * add a new manager
	 * @param array $data
	 * @return boolean
	 */
	public function addArticles($data = array())
    {
		if (!empty($data))
		{
			$para['ac_id']            = isset($data['ac_id'])?trim($data['ac_id']):'';
			$para['a_title']          = isset($data['a_title'])?trim($data['a_title']):'';
			$para['a_info']           = isset($data['a_info'])?trim($data['a_info']):'';
			$para['a_in_time']        = isset($data['a_in_time'])?$data['a_in_time']:time();
			$para['a_link']           = isset($data['a_link'])?trim($data['a_link']):'';
			$para['a_author']         = isset($data['a_author'])?trim($data['a_author']):'';
			$para['a_last_editor']    = $para['a_author'];
			$para['a_last_edit_time'] = $para['a_in_time'];
			$para['a_img']            = isset($data['a_img'])?trim($data['a_img']):'';
			$para['a_top']            = isset($data['a_top'])?trim($data['a_top']):'0';
			$para['a_recommend']      = isset($data['a_recommend'])?trim($data['a_recommend']):'0';
			$para['a_status']         = isset($data['a_status'])?trim($data['a_status']):'0';
			$para['a_start_time']     = isset($data['m_start_time'])?$data['m_start_time']:'0';
			$para['a_end_time']       = isset($data['m_end_time'])?$data['m_end_time']:'0';
			$para['r_id']             = isset($data['r_id'])?trim($data['r_id']):'0';
			$para                     = deepArrayFilter($para, '');
			$id                       = $this->manager->addData($para);
			if ($id)
			{
				$para2['a_id']   = $id;
				$para2['a_desc'] = isset($data['a_desc'])?trim($data['a_desc']):'';
				$para2           = deepArrayFilter($para2, '');
				$desc            = $this->articles_ext->addData($para2);
				if ($desc)
				{
					return $id;
				}
				$this->articles_ext->delData($id);//delete article
			}
			return false;
		}
		return false;
	}

	/**
	 * modify a manager by params
	 * @param array $data
	 * @return boolean
	 */
	public function editArticles($data = array())
    {
		if (!empty($data))
		{
			$a_id = isset($data['a_id'])?intval($data['a_id']):0;
			if (0 >= $a_id)
			{
				return false;
			}

			$para['ac_id']            = isset($data['ac_id'])?trim($data['ac_id']):'';
			$para['a_title']          = isset($data['a_title'])?trim($data['a_title']):'';
			$para['a_info']           = isset($data['a_info'])?trim($data['a_info']):'';
			$para['a_in_time']        = isset($data['a_in_time'])?$data['a_in_time']:time();
			$para['a_link']           = isset($data['a_link'])?trim($data['a_link']):'';
			$para['a_author']         = isset($data['a_author'])?trim($data['a_author']):'';
			$para['a_last_editor']    = $para['a_author'];
			$para['a_last_edit_time'] = $para['a_in_time'];
			$para['a_img']            = isset($data['a_img'])?trim($data['a_img']):'';
			$para['a_top']            = isset($data['a_top'])?trim($data['a_top']):'0';
			$para['a_recommend']      = isset($data['a_recommend'])?trim($data['a_recommend']):'0';
			$para['a_status']         = isset($data['a_status'])?trim($data['a_status']):'0';
			$para['a_start_time']     = isset($data['m_start_time'])?$data['m_start_time']:'0';
			$para['a_end_time']       = isset($data['m_end_time'])?$data['m_end_time']:'0';
			$para['r_id']             = isset($data['r_id'])?trim($data['r_id']):'0';
			$para                     = deepArrayFilter($para, '');
			$id                       = $this->articles->updateData($para, array('a_id' => $a_id));
			if ($id)
			{
				$para2['a_desc'] = isset($data['a_desc'])?trim($data['a_desc']):'';
				$para2           = deepArrayFilter($para2, '');
				$desc            = $this->articles_ext->updateData($para2, array('a_id' => $a_id));
				return $desc;
			}
			return false;
		}
		return false;
	}

	/**
	 * delete manager by params
	 * @param unknown $data
	 * @return boolean
	 */
	public function delArticles($data)
    {
		if (!empty($data))
		{
			if (!is_array($data))
			{
				return $this->articles->delData($data);//更新单条
			}
			foreach ($data as $key => $val)
			{
				if (0 < intval($val))
				{
					$para[] = $val;
				}
			}
			if (empty($para))
			{
				return false;
			}//多个id同时操作   删除文章时候 只删除文章表 文章内容表暂留 之后用补丁来解决
			return $this->articles->delData(array(
					'walk'   => array(
						'where' => array(
							'in'   => array(
								'a_id', implode(',', $para),
							),
						),
					),
				)
			);
		}
		return false;
	}

	/**
	 * validata manager by params
	 * @param array $data
	 * @return int
	 */
	public function countArticles($data = array())
    {
		$para = array();
		if (!empty($data))
		{
			$para['a_id']        = isset($data['a_id'])?intval($data['a_id']):'';
			$para['ac_id']       = isset($data['ac_id'])?trim($data['ac_id']):'';
			$a_title             = isset($data['a_title'])?trim($data['a_title']):'';
			$a_info              = isset($data['a_info'])?trim($data['a_info']):'';
			$a_link              = isset($data['a_link'])?trim($data['a_link']):'';
			$para['a_author']    = isset($data['a_author'])?trim($data['a_author']):'';
			$para['a_top']       = isset($data['a_top'])?trim($data['a_top']):'';
			$para['a_recommend'] = isset($data['a_recommend'])?trim($data['a_recommend']):'';
			$para['a_status']    = isset($data['a_status'])?trim($data['a_status']):'';
			$para['r_id']        = isset($data['r_id'])?trim($data['r_id']):'';
			$start_time          = isset($data['start_time'])?$data['start_time']:'';
			$end_time            = isset($data['end_time'])?$data['end_time']:'';
			$para['where']       = ' 1 ';
			if (isset($data['where']))
			{
				$para['where'] .= ' AND '.$data['where'];
			}

			if ('' != $start_time)
			{
				$para['where'] .= ' AND a_start_time >='.$start_time;
			}

			if ('' != $end_time)
			{
				$para['where'] .= ' AND a_start_time <= '.$end_time;
			}

			if ('' != $a_title)
			{
				$para['where'] .= ' AND a_title LIKE "%'.$a_title.'%"';
			}

			if ('' != $a_info)
			{
				$para['where'] .= ' AND a_info LIKE "%'.$a_info.'%"';
			}

			if ('' != $a_link)
			{
				$para['where'] .= ' AND a_link LIKE "%'.$a_link.'%"';
			}

			$para = deepArrayFilter($para, '');
		}
		return $this->manager->countData($para);
	}

}
