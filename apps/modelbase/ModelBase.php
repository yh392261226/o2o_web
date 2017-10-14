<?php
namespace MMODEL;

/**
 * @action   model层基础类
 */

class ModelBase extends \Swoole\Model
{
    public $primary;
    protected $allow_delete = true;
    public $select = '*';

    public function __construct(\Swoole $swoole, $db_key = 'master')
    {
        parent::__construct($swoole, $db_key);
    }

    /**
     * @param array $data
     * @return array
     * @author Me
     * @desc 多条详情
     *https://worthy.gitbooks.io/swoole-framework-db-api/content/%E6%9F%A5%E8%AF%A2.html
     */
    public function getDatas($data = array())
    {
        if (!empty($data))
        {
            if (is_array($data))
            {
                $val = isset($data['val']) ? $data['val'] : '';
                $key = isset($data['key']) ? $data['key'] : '';
                if ($val && $key)
                {
                    return $this->get($val, $key)->get();
                    //永远走不到
                    //if (count($data) > 2 || isset($data['key']) || isset($data['val']))
                    //{
                    //    return $this->select($paras['fields'])->where($paras['where'])->fetch();
                    //}
                }
                else
                {
                    if (isset($data['pager']) && $data['pager'])
                    {
                        $pager = null;
                        unset($data['pager']);
                        return array('data' => $this->gets($data, $pager), 'pager' => $pager);
                    }
                    else
                    {
                        return array('data' => $this->gets($data));
                    }
                }
            }
            return $this->get($data)->get();//直接传主键值
        }
        return $this->gets();
    }

    /**
     * @param array $data 一维数组 键值对  name=123
     * @return bool
     * @author Ross
     * @desc 删除管理员
     */
    public function delData($data = array())
    {
        if ($this->allow_delete == false)
        {
            return false;
        }

        if (!empty($data))
        {
            if (!is_array($data))
            {
                return $this->del($data);
            }
            else
            {
                $type = getArrayDeep($data, 1);//echo $type;
                if ($type == 1)
                {
                    $val = isset($data['val']) ? $data['val'] : '';
                    $key = isset($data['key']) ? $data['key'] : '';
                    return $this->del($val, $key);
                }
                else
                {
                    return $this->dels($data);
                }
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @param array $fields
     * @return boolean|number
     * @添加数据
     */
    public function addData($data = array(), $fields = array())
    {
        if (!empty($data))
        {
            $type = getArrayDeep($data);
            if ($type)
            {
                return $this->put($data);
            }
            else
            {
                if (count($data) > 0 && count($fields) > 0)
                {
                    return $this->puts($fields, $data);
                }
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @param array $params
     * @param number $type
     * @return boolean
     * @更新数据
     */
    public function updateData($data = array(), $params = array())
    {
        if (!empty($data) && !empty($params))
        {
            $type = getArrayDeep($params);
            if ($type)
            {
                $where = isset($params['where']) ? trim($params['where']) : '';
                return $this->set($params[$this->primary], $data, $where);
            }
            return $this->sets($data, $params);
        }
    }

    /**
     * @param array $data
     * @return boolean|number
     * @查询条数
     */
    public function countData($data = array())
    {
        return $this->count($data);
    }

    /**
     * @return int
     * @获取最后插入ID
     */
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * @return int
     * @设置查询字段
     */
    public function setFields($data)
    {
        if (!empty($data))
        {
            if (is_array($data))
            {
                $this->select = implode(',', $data);
            }
            $this->select = $data;
        }
        $this->select = '*';
    }

    public function queryData($sql = '')
    {
        if ('' != trim($sql))
        {
            return $this->db->query($sql);
        }
        return false;
    }
}
