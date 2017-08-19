<?php
namespace CLASSES;

use Swoole;

/**
 * @action   model层基础类
 */
class ModelBase extends Swoole\Model
{
    protected $paras = array(
        'where' => '1',
        'fields' => '*',
        'limit_start' => 1,
        'limit_end' => PAGESIZE,
    );

    /**
     * @param array $data
     * @return bool
     * @author Me
     * @desc 设置参数
     */
    protected function setdatas($data = array())
    {
        if (!empty($data)) {
            $paras = array();
            foreach ($data as $key => $val) {
                if (isset($val)) {
                    $paras[$key] = $val;
                }
            }
            $this->paras = $paras;
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
        $result['data'] = $this->gets($data, $pager);
        //$total_num表示符合条件的总的记录条数
        $result['page']['total_num'] = $pager->total;
        //$total_page表示总共有几页数据
        $result['page']['total_page'] = (int) $pager->totalpage;
        //$current_page表示当前取的是第几页的数据
        $result['page']['current_page'] = $pager->page;
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
                $val = isset($data[$this->primary])? $data[$this->primary] : $data['val'];
                $key = isset($data['key']) ? $data['key'] : $this->primay;
                return $this->del($val, $key);
            } else {
                return $this->dels($data);
            }
        }
    }

    /**
     * @param array $data
     * @return bool
     * @author Ross
     * @desc 新增/修改管理员
     */
    public function updateData($data = array())
    {
        if (!empty($data)) {
            $where = $data['where'];
            unset($data['where']);
            $id = $data[$this->primary];
            unset($data[$this->primary]);
            $this->setdatas($data);
            return $this->db->update($id, $data, $this->table, $where);
        }
        return false;
    }
    /**
     * @author 户连超
     * @e-mail zrkjhlc@gmail.com
     * @date   2017-08-16
     * @param  array             $data [description]
     * @return [type]                  [description]
     */
    public function saveData($data = array())
    {
        if (!empty($data)) {
            $this->setdatas($data);
            return $this->db->insert($data, $this->table);
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
