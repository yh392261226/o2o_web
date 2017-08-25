<?php
namespace MDAO;

class Managers extends \MDAO\DaoBase
{
    public $managers = null;
    public $manager_privileges_group = null;
    public $manager_privileges_modules = null;
    
    public function __construct()
    {
        $this->managers = model("Managers");
        $this->managers_privileges_group = model("Managers_privileges_group");
        $this->manager_privileges_modules = model('Manager_privileges_modules');
    }
    
    /**
     * list managers by params 
     * @param array|string $data
     * @return array
     */
    public function listManagers($data = array())
    {
        $para = array();
        $para['pager']          = isset($data['pager']) ? $data['pager'] : true;
        $para['page']          = isset($data['page']) ? $data['page'] : 1;
        $para['pagesize']       = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;
        if (!empty($data))
        {
            $para['m_status']   = isset($data['m_status']) ? intval($data['m_status']) : '';
            $para['m_name']     = isset($data['m_name']) ? trim($data['m_name']) : '';
            $para['m_inip']     = isset($data['m_inip']) ? trim($data['m_inip']) : '';
            $para['mpg_id']     = isset($data['mpg_id']) ? trim($data['mpg_id']) : '';
            $start_time         = isset($data['start_time']) ? $data['start_time'] : '';
            $end_time           = isset($data['end_time']) ? $data['end_time'] : '';
            $para['where']      = '';
            if (isset($data['where'])) $para['where']  .= $data['where'];
            if ('' != $start_time) $para['where']  .= 'm_in_time >=' . $start_time;
            if ('' != $end_time) $para['where']  .=' AND m_in_time <= ' . $end_time;
            $para['page']          = isset($data['page']) ? $data['page'] : 1;
            $para['pager']      = isset($data['pager']) ? $data['pager'] : true;
            $para['pagesize']   = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;
                        
        }
        //print_r($para);exit;
        $para = @array_filter($para, 'strlen'); //remove the false value of the array
        return $this->managers->getDatas($para);
    }

    /**
     * info manager by params
     * @param unknown $data
     * @return unknown|array
     */
    public function infoManager($data = array())
    {
        if (!empty($data))
        {
            return $this->managers->getDatas($data);
        }
        return array();
    }
    
    /**
     * add a new manager
     * @param array $data
     * @return boolean
     */
    public function addManager($data = array())
    {
        if (!empty($data))
        {
            $para['m_name']       = isset($data['m_name']) ? trim($data['m_name']) : '';
            if ('' == $para['m_name']) return false;
            $para['m_pass']       = isset($data['m_pass']) ? encyptPassword(trim($data['m_pass'])) : '';
            if ('' == $para['m_pass'] || $para['m_pass'] == encyptPassword('')) return false;
            $para['m_status']     = isset($data['m_status']) ? intval($data['m_status']) : 0;
            $para['m_in_time']    = isset($data['m_in_time']) ? $data['m_in_time'] : time();
            $para['m_inip']       = isset($data['m_inip']) ? $data['m_inip'] : getIp('0');
            $para['m_author']     = isset($data['m_author']) ? $data['m_author'] : $_SESSION['m_id'];
            $para['m_last_time']  = $para['m_in_time'];
            $para['m_last_ip']    = $para['m_inip'];
            $para['m_last_editor']= $para['m_author'];
            $para['mpg_id']       = isset($data['mpg_id']) ? intval($data['mpg_id']) : '';
            $para['m_start_time'] = isset($data['m_start_time']) ? $data['m_start_time'] : '0';
            $para['m_end_time']   = isset($data['m_end_time']) ? $data['m_end_time'] : '0';
            $para = deepArrayFilter($para, '');
            return $this->managers->addData($para);
        }
        return false;
    }
    
    /**
     * modify a manager by params
     * @param array $data
     * @return boolean
     */
    public function editManager($data = array())
    {
        if (!empty($data))
        {
            $m_id         = isset($data['m_id']) ? intval($data['m_id']) : 0;
            if (0 >= $m_id) return false;
            $para['m_pass']       = isset($data['m_pass']) ? encyptPassword(trim($data['m_pass'])) : '';
            if ('' == $para['m_pass'] || $para['m_pass'] == encyptPassword('')) return false;
            $para['m_status']     = isset($data['m_status']) ? intval($data['m_status']) : '';
            $para['m_last_time']  = isset($data['m_last_time']) ? $data['m_last_time'] : time();
            $para['m_last_ip']    = isset($data['m_last_ip']) ? $data['m_last_ip'] : getIp('0');
            $para['m_last_editor']= isset($data['m_last_editor']) ? $data['m_last_editor'] : $_SESSION['m_id'];
            $para['mpg_id']       = isset($data['mpg_id']) ? intval($data['mpg_id']) : '';
            $para['m_start_time'] = isset($data['m_start_time']) ? $data['m_start_time'] : '0';
            $para['m_end_time']   = isset($data['m_end_time']) ? $data['m_end_time'] : '0';
            $para = deepArrayFilter($para, '');
            return $this->managers->updateData($para, array('m_id' => $m_id));
        }
        return false;
    }
    
    /**
     * delete manager by params
     * @param unknown $data
     * @return boolean
     */
    public function delManagers($data)
    {
        if (!empty($data)) 
        {
            if (!is_array($data))
            {
                return $this->managers->delData($data); //更新单条
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
            }//多个id同时更新
            return $this->managers->delData(array(
                'walk' => array(
                    'where' => array(
                        'in' => array(
                            'm_id', implode(',', $para)
                        )
                    )
                )
            )
            );
        }
        return false;
    }

    public function delManagers2($data)
    {
        if (!empty($data))
        {
            if (!is_array($data))
            {
                return $this->managers->delData2($data); //更新单条
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
            }//多个id同时更新
            return $this->managers->delData2(array(
                    'walk' => array(
                        'where' => array(
                            'in' => array(
                                'm_id', implode(',', $para)
                            )
                        )
                    )
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
    public function countManagers($data = array())
    {
        $para = array();
        if (!empty($data))
        {
            $para['m_id']         = isset($data['m_id']) ? intval($data['m_id']) : '';
            $para['m_name']       = isset($data['m_name']) ? trim($data['m_name']) : '';
            $para['m_status']     = isset($data['m_status']) ? intval($data['m_status']) : '';
            $para['mpg_id']       = isset($data['mpg_id']) ? intval($data['mpg_id']) : '';
            $para['m_author']     = isset($data['m_author']) ? $data['m_author'] : '';
            $para['m_inip']       = isset($data['m_inip']) ? $data['m_inip'] : '';
            $para['m_last_ip']    = isset($data['m_last_ip']) ? $data['m_last_ip'] : '';
            $para['m_in_time']    = isset($data['m_in_time']) ? $data['m_in_time'] : '';
            $para['m_last_time']  = isset($data['m_last_time']) ? $data['m_last_time'] : '';
            $para['m_last_editor']= isset($data['m_last_editor']) ? $data['m_last_editor'] : '';
            $para['m_start_time'] = isset($data['m_start_time']) ? $data['m_start_time'] : '';
            $para['m_end_time']   = isset($data['m_end_time']) ? $data['m_end_time'] : '';
            $start_time         = isset($data['start_time']) ? $data['start_time'] : '';
            $end_time           = isset($data['end_time']) ? $data['end_time'] : '';
            $para['where']      = '';
            if ('' != $start_time) $para['where']  .= 'm_in_time >=' . $start_time;
            if ('' != $end_time) $para['where']  .=' AND m_in_time <= ' . $end_time;

            $para = deepArrayFilter($para, '');
        }
        return $this->managers->countData($para);
    }

    /**
     * @param $data
     * @return bool|mixed
     * @ 登陆
     */
    public function login($data)
    {
        if (!empty($data))
        {
            if (!isset($data['m_name']) || '' == trim($data['m_name']) || !isset($data['m_pass']) || '' == trim($data['m_pass']))
            {
                return false;
            }
            $info = array();
            $info = $this->infoManager(array('m_name' => $data['m_name'], 'm_pass' => $data['m_pass']));
            if (isset($info[0]) && !empty($info[0]))
            {
                return $info[0];
            }
            return false;
        }
        return false;
    }

    /**
     * @param array $data
     * @return mixed
     * @权限组列表
     */
    public function listManagersPrivatesGroup($data = array())
    {
        $para = array();
        $para['pager']          = isset($data['pager']) ? $data['pager'] : true;
        $para['page']          = isset($data['page']) ? $data['page'] : 1;
        $para['pagesize']       = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;
        if (!empty($data))
        {
            $para['mpg_id']     = isset($data['mpg_id']) ? intval($data['mpg_id']) : '';
            $para['mpg_name']   = isset($data['mpg_name']) ? intval($data['mpg_name']) : '';
            $para['mpg_status'] = isset($data['mpg_status']) ? trim($data['mpg_status']) : '';
            $para['mpg_author'] = isset($data['mpg_author']) ? trim($data['mpg_author']) : '';
            $para['mpg_editor'] = isset($data['mpg_editor']) ? trim($data['mpg_editor']) : '';
            $start_time         = isset($data['start_time']) ? $data['start_time'] : '';
            $end_time           = isset($data['end_time']) ? $data['end_time'] : '';
            $para['where']      = '';
            if (isset($data['where'])) $para['where']  .= $data['where'];
            if ('' != $start_time) $para['where']  .= 'mpg_in_time >=' . $start_time;
            if ('' != $end_time) $para['where']  .=' AND mpg_in_time <= ' . $end_time;
            $para['page']          = isset($data['page']) ? $data['page'] : 1;
            $para['pager']      = isset($data['pager']) ? $data['pager'] : true;
            $para['pagesize']   = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;

        }
        //print_r($para);exit;
        $para = @array_filter($para, 'strlen'); //remove the false value of the array
        return $this->managers_privileges_group->getDatas($para);
    }

    /**
     * info managers_private_group by params
     * @param unknown $data
     * @return unknown|array
     */
    public function infoManagersPrivatesGroup($data = array())
    {
        if (!empty($data))
        {
            $info = $this->managers_privileges_group->getDatas($data);
            if (!empty($info['mpm_ids']))
            {
                $info['modules'] = $this->listManager_privileges_modules->getDatas(
                    array('where' => 'mpm_id IN ("' . $info['mpm_ids'] . '")',
                    'pager' => false
                    ));
            }
            return $info;
        }
        return array();
    }


    /**
     * list managers privileges modules
     * @param array $data
     * @return mixed
     */
    public function listManager_privileges_modules($data =array())
    {
        $para = array();
        $para['pager']          = isset($data['pager']) ? $data['pager'] : true;
        $para['page']           = isset($data['page']) ? $data['page'] : 1;
        $para['pagesize']       = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;
        if (!empty($data))
        {
            $para['mpm_id']     = isset($data['mpm_id']) ? intval($data['mpm_id']) : '';
            $para['mpm_name']   = isset($data['mpm_name']) ? intval($data['mpm_name']) : '';
            $para['mpm_status'] = isset($data['mpm_status']) ? trim($data['mpm_status']) : '';
            $para['mpm_value']  = isset($data['mpm_value']) ? trim($data['mpm_value']) : '';
            $para['where']      = '';
            if (isset($data['where'])) $para['where']  .= $data['where'];
            $para['page']          = isset($data['page']) ? $data['page'] : 1;
            $para['pager']      = isset($data['pager']) ? $data['pager'] : true;
            $para['pagesize']   = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;

        }
        //print_r($para);exit;
        $para = @array_filter($para, 'strlen'); //remove the false value of the array
        return $this->manager_privileges_modules->getDatas($para);
    }

    public function __destruct()
    {
        unset($this->managers, $this->manager_privileges_group, $this->manager_privileges_modules);
    }
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              