<?php
namespace CLASSES;

use Swoole;

/**
 * @action   model层基础类
 */
class ModelBase extends Swoole\Model
{
    protected $paras = array(
        'where' => array('1'),
        'pagesize' => PAGESIZE,
    );

    /**
     * @param array $data
     * @return bool
     * @author Me
     * @desc 设置参数
     */
    private function setdatas($data = array())
    {
        if (!empty($data)) {
            $paras = array();
            foreach ($data as $key => $val) {
                if (isset($val)) {
                    $paras[$key] = $val;
                }
            }
            $this->paras += $paras;
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     * @return array
     * @author Me
     * @desc 多条详情
     */
    public function listDatas($data = array())
    {
        $this->setdatas($data);
        $pager = null;
        $result['data'] = $this->gets($this->paras, $pager);
        //$total_num表示符合条件的总的记录条数
        $result['page']['total_num'] = $pager->total;
        //$total_page表示总共有几页数据
        $result['page']['total_page'] = (int) $pager->totalpage;
        //$current_page表示当前取的是第几页的数据
        $result['page']['current_page'] = $pager->page;

        $pager->set_class("first","btn btn-white");
        $pager->set_class("previous","btn btn-white");
        $pager->set_class("next","btn btn-white");
        $pager->set_class("last","btn btn-white");
        $result['page'] = $pager->render();
        return $result;
    }

    /**
     * @param array $data
     * @return array
     * @author Me
     * @desc 单条详情
     */
    public function infoDatas($data = array())
    {
        $this->setdatas($data);
        $paras = $this->paras;
        return $this->select($paras['fields'])->where($paras['where'])->fetch();
    }

    /**
     * @param array $data int $type
     * @return bool
     * @author Ross
     * @desc 删除管理员
     */
    public function delData($data = array(), $type = 0)
    {
        if (!empty($data)) {
            $this->setdatas($data);
            if ($type == 0) {
                return $this->del($data[$this->primary], $this->primary);
            } else {
                return $this->dels($data);
            }
        }
    }
    /**
     * @param array $data
     * @param array $fields
     * @return boolean|number
     * @添加数据
     */
    public function addData($data = array(), $fields = array())
    {
        if (count($data) > 0 && count($fields) > 0) 
        {
            return $this->puts($fields, $data);
        }
        return $this->put($data);
    }
    
    /**
     * @param array $data
     * @param array $params
     * @param number $type
     * @return boolean
     * @更新数据
     */
    public function updateData($data = array(), $params = array(), $type = 0)
    {
        if (!empty($data) && !empty($params))
        {
            if (intval($type) == 0) //更新单条
            {
                return $this->set($params['id'], $data, $params['where']);
            }
            return $this->sets($data, $params);
        }
        return false;
    }
    /**
     * [lastInsertId description] 获取刚添加的id
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-16
     * @return [type]            [description]
     */
    public function lastInsertId()
    {
        return $this->db->_db->insert_id;
    }
}
