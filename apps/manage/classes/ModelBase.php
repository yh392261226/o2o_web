<?php
namespace CLASSES;

use Swoole;
/**
 * @action   model层基础类
 */
class ModelBase extends Swoole\Model
{
    protected $paras = array(
        'where'       => array('1'),
        'fields'      => '*',
        'limit_start' => 1,
        'limit_end'   => PAGESIZE,
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
        $pager          = null;
        $result['data'] = $this->gets($data, $pager);
        //$total_num表示符合条件的总的记录条数
        $result['page']['total_num'] = $pager->total;
        //$total_page表示总共有几页数据
        $result['page']['total_page'] = (int) $pager->totalpage;
        //$current_page表示当前取的是第几页的数据
        $result['page']['current_page'] = $pager->page;
        $result['page']                 = $pager->render();
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
     * @return bool
     * @author Ross
     * @desc 新增/修改管理员
     */
    public function saveData($data = array())
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
}
