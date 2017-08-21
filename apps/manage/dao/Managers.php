<?php
namespace MDAO;

class Managers
{
    public $manager = null;
    
    public function __construct()
    {
        $this->manager = model("Managers");
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
        $para['test']['test']   = '01';
        if (!empty($data))
        {
            $para['m_status']   = isset($data['m_status']) ? intval($data['m_status']) : '';
            $para['m_name']     = isset($data['m_name']) ? trim($data['m_name']) : '';
            $para['m_inip']     = isset($data['m_inip']) ? trim($data['m_inip']) : '';
            $para['mpg_id']     = isset($data['mpg_id']) ? trim($data['mpg_id']) : '';
            $start_time         = isset($data['start_time']) ? $data['start_time'] : '';
            $end_time           = isset($data['end_time']) ? $data['end_time'] : '';
            $para['where']      = '';
            if ('' != $start_time) $para['where']  .= 'm_in_time >=' . $start_time;
            if ('' != $end_time) $para['where']  .=' AND m_in_time <= ' . $end_time;
            $para['page']          = isset($data['page']) ? $data['page'] : 1;
            $para['pager']      = isset($data['pager']) ? $data['pager'] : true;
            $para['pagesize']   = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;
                        
        }
        $para = deepArrayFilter($para, 'strlen'); //remove the false value of the array
        return $this->manager->getDatas($para);
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
            return $this->manager->getData($data);
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
            $para['m_last_time']  = $para['m_in_time'];
            $para['m_last_ip']    = $para['m_inip'];
            $para['m_last_editor']= $para['m_author'];
            $para['m_author']     = isset($data['m_author']) ? $data['m_author'] : $_SESSION['m_id'];
            $para['mpg_id']       = isset($data['mpg_id']) ? intval($data['mpg_id']) : '';
            $para['m_start_time'] = isset($data['m_start_time']) ? $data['m_start_time'] : '0';
            $para['m_end_time']   = isset($data['m_end_time']) ? $data['m_end_time'] : '0';
            $para = deepArrayFilter($para, 'strlen');
            return $this->manager->addData($para);
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
            $para = deepArrayFilter($para, 'strlen');
            return $this->manager->updateData($para, array('m_id' => $m_id));
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
                return $this->manager->delData($data);
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
            }
            return $this->manager->delData(array('walk' => array('where' => array('in' => $para))));
        }
        return false;
    }
    
    /**
     * validata manager by params
     * @param array $data
     * @return int
     */
    public function validata($data = array())
    {
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
            $para['m_last_editor']= isset($data['m_last_editor']) ? $data['m_last_editor'] : $_SESSION['m_id'];
            $para['m_start_time'] = isset($data['m_start_time']) ? $data['m_start_time'] : '';
            $para['m_end_time']   = isset($data['m_end_time']) ? $data['m_end_time'] : '';
            $start_time         = isset($data['start_time']) ? $data['start_time'] : '';
            $end_time           = isset($data['end_time']) ? $data['end_time'] : '';
            $para['where']      = '';
            if ('' != $start_time) $para['where']  .= 'm_in_time >=' . $start_time;
            if ('' != $end_time) $para['where']  .=' AND m_in_time <= ' . $end_time;

            $para = deepArrayFilter($para, 'strlen');
            return $this->manager->countData($para);
        }
        return 0;
    }
    
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              