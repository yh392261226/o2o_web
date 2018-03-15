<?php
namespace WDAO;

class Bouns_data extends \MDAOBASE\DaoBase
{
    public function __construct()
    {
        parent::__construct(array('table' => 'Bouns_data'));
    }

    public function infoBounsData($data = array())
    {
        $info = array();
        if (isset($data['bd_id']) || isset($data['key']))
        {
            if (isset($data['bd_id']))
            {
                $info = parent::infoData(intval($data['bd_id']));
            }
            elseif (isset($data['key']))
            {
                $info = parent::infoData(array('key' => trim($data['key']), 'val' =>  $data['val']));
            }
        }

        if (!empty($info))
        {
            $param = array();
            $param['bt'] = $info['bt_id'];
            $param['b'] = $info['b_id'];
            $bouns_type_dao = new \WDAO\Bouns_type();
            $info['type'] = $bouns_type_dao->infoData($param['bt']);
            if (!empty($info['type']))
            {
                $info = $info + $info['type'];
                unset($info['type']);
            }
            $bouns_dao = new \WDAO\Bouns();
            $info['bouns'] = $bouns_dao->infoData($param['b']);
            if (!empty($info['bouns']))
            {
                $info = $info + $info['bouns'];
                unset($info['bouns']);
            }
            return $info;
        }
        else
        {
            return array();
        }
    }

    /**
     * 归还抵扣券给用户
     * @param array $data
     * @return bool
     */
    public function rebackBounsToUser($data = array())
    {
        if (!empty($data))
        {
            $param = array();
            if (isset($data['bd_author']) && intval($data['bd_author']) > 0) $param['bd_author'] = intval($data['bd_author']); //必有字段
            if (!isset($param['bd_author']) || $param['bd_author'] <= 0)
            {
                return false;
            }
            if (isset($data['b_id']) && intval($data['b_id']) > 0) $param['b_id'] = intval($data['b_id']);
            if (isset($data['bd_id']) && intval($data['bd_id']) > 0) $param['bd_id'] = intval($data['bd_id']);
            if (isset($data['bd_serial']) && '' != trim($data['bd_serial'])) $param['bd_serial'] = trim($data['bd_serial']);
            if (!empty($param))
            {
                return $this->updateData(array('bd_use_time' => 0), $param);
            }
        }
        return false;
    }

}