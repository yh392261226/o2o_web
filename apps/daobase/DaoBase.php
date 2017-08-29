<?php
namespace MDAOBASE;

class DaoBase
{
    public  $table   = '';
    private $handler = null;
    private $fields   = array();

    public function __construct($data = array())
    {
        if (isset($data['table']))
        {
            $this->table = $data['table'];
            $this->handler = model($this->table);
        }
        else
        {
            exit('Can not connect to database::table !');
        }

        $this->getFields(); //get all the fields of the table
    }

    /**
     * 返回where条件拼接
     * @param array $data
     * @return array
     */
    public function createWhere($data =array())
    {
        $param = array();
        if (is_array($data) && !empty($data))
        {
            //必有
            $param['pager']    = isset($data['pager']) ? $data['pager'] : true;
            $param['page']     = isset($data['page']) ? $data['page'] : 1;
            $param['pagesize'] = isset($data['pagesize']) ? $data['pagesize'] : PAGESIZE;
            $param['key']      = isset($data['key']) ? $data['key'] : '';
            $param['val']      = isset($data['val']) ? $data['val'] : '';
            $param['where']    = ' 1 ';
            unset($data['pager'], $data['page'], $data['pagesize'], $data['key'], $data['val']);
            if ($param['key'] && $param['val']) //如果有单独的key 及 val 那么就是取单条 其他条件都去掉
            {
                return array('key' => $param['key'], 'val' => $param['val']);
            }
            else
            {
                unset($param['key'], $param['val']);
            }
            //正常的where条件组装 ： where a=1 and b=2 and c=3
            foreach ($this->fields as $key => $val)
            {
                if (isset($data[$val]) && '' !== $data[$val])
                {
                    $param[$val] = $data[$val];
                }
            }

            //进入规则调整 在规则中的 需要unset掉在上面正常的where条件
            foreach ($data as $key => $val)
            {
                //print_r($val);echo "<br>\n";
                if (isset($val))
                {
                    $tmpkey = array();
                    $tmpkey = explode('_',$key);
                    unset($tmpkey[0]);
                    $rule_key = searchKey(implode($tmpkey), \Swoole::$php->config['fields_type']);
                    //echo $rule_key;
                    switch($rule_key)
                    {
                        case 'primary':
                            //equal, =, in, notin
                            if (isset($val['type']) && in_array($val['type'], array('in', 'notin', 'equal', '=')))
                            {
                                if ($val['type'] == '=') $val['type'] = 'equal';
                                if (is_array($val['value'])) $val['value'] = implode(',', $val['value']);
                                $param['walk']['where'][$val['type']] = array($key, $val['value']);
                                unset($param[$key]);
                            }
                            break;
                        case 'blurred':
                            //like, likebegin, likeend
                            /**
                             * $data['m_name'] = array('type' => 'like', 'value' => 'admin');
                             */
                            if (isset($val['type']) && in_array($val['type'], array('like', 'likebegin', 'likeend')))
                            {
                                if ($val['type'] == 'like')
                                {
                                    $val['value'] = '%' . $val['value'] . '%';
                                }
                                if ($val['type'] == 'likebegin')
                                {
                                    $val['value'] = $val['value'] . '%';
                                }
                                if ($val['type'] == 'likeend')
                                {
                                    $val['value'] = '%' . $val['value'];
                                }

                                $param['walk']['where']['like'] = array($key, $val['value']);
                                unset($param[$key]);
                            }
                            break;
                        case 'interval':
                            //beween .. and .., <, <=, >=, >
                            /**
                             * $data['m_in_time'] = array('type' => 'gt', 'value' => 1234567890, 'gt_value' => 2345678910);
                             */
                            if (is_array($val))
                            {
                                $deep = getArrayDeep($val, true);
                                if ($deep == 1) //一维数组 直接赋值
                                {
                                    if (isset($val['type']) && in_array($val['type'], array('gt', 'lt', 'ge', 'le')))
                                    {
                                        $explain = array('gt' => '>', 'lt' => '<', 'ge' => '>=', 'le' => '<=');
                                        $param['where'] .= ' and ' . $key . $explain[$val['type']] . $val[$val['type'].'_value'];
                                        unset($param[$key]);
                                    }
                                }
                                else //多维数组
                                {
                                    $explain = array('gt' => '>', 'lt' => '<', 'ge' => '>=', 'le' => '<=');
//print_r($val);exit;
                                    if (isset($val[0]['type']) && in_array($val[0]['type'], array('gt', 'lt', 'ge', 'le')))
                                    {
                                        $param['where'] .= ' and ' . $key . $explain[$val[0]['type']] . $val[0][$val[0]['type'].'_value'];
                                        unset($param[$key]);
                                    }
                                    if (isset($val[1]['type']) && in_array($val[1]['type'], array('gt', 'lt', 'ge', 'le')))
                                    {
                                        $param['where'] .= ' and ' . $key . $explain[$val[1]['type']] . $val[1][$val[1]['type'].'_value'];
                                        unset($param[$key]);
                                    }
                                }
                            }
                            break;
                    }
                }
            }

        }

        return $param;
    }

    /**
     * @return mixed|\Swoole\Database\MySQLiRecord
     * @表字段信息列表
     */
    public function getFields()
    {
        $fields = array();
        //引入表信息文件
        if (defined('CACHEDB')) include_once CACHEDB . 'db_fields.php';

        if (!empty($fields[$this->table]))
        {
            return $fields[$this->table];
        }
        else
        {
            $fields = $this->handler->desc();
            if (!empty($fields))
            {
                foreach ($fields as $key => $val)
                {
                    $this->fields[] = $val['Field'];
                }
            }
            return $this->fields;
        }
    }

    public function listData($data = array())
    {
        if (isset($data['fields']))
        {
            $this->handler->select = $data['fields'];
            unset($data['fields']);
        }
        else
        {
            $this->handler->select = implode(',', $this->fields);
        }
        //print_r($data);
        $param = $this->createWhere($data);
        return $this->handler->getDatas($param);
    }

    public function infoData($data)
    {
        if (isset($data['fields']))
        {
            $this->handler->select = $data['fields'];
            unset($data['fields']);
        }
        else
        {
            $this->handler->select = implode(',', $this->fields);
        }
        $param = $this->createWhere($data);
        if (empty($param))
        {
            $param = $data;
        }
        return $this->handler->getDatas($param);
    }

    public function delData($data = array())
    {
        $param = $this->createWhere($data);
        unset($param['page']);
        unset($param['pager']);
        unset($param['pagesize']);
        if (empty($param))
        {
            $param = $data;
        }
        return $this->handler->delData($param);
    }

    public function addData($data = array(), $fields = array())
    {
        return $this->handler->addData($data, $fields);
    }

    public function updateData($data = array(), $param = array())
    {
        return $this->handler->updateData($data, $param);
    }

    public function countData($data = array())
    {
        $param = $this->createWhere($data);
        unset($param['pager']);
        return $this->handler->countData($param);
    }

    public function queryData($sql = '')
    {
        return $this->handler->db->query($sql);
    }
}
