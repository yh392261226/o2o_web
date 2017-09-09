<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-29 16:21:05
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-03 16:37:12
 */
namespace App\Controller;
class Demo extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
        $this->managers_dao = new \MDAO\Managers();
    }
    public function test()
    {
        if (DEBUG)
        {
            $this->db->debug = 1;
            //$this->showTrace(1);
            //$this->showTime();
        }
        //Regions修改数据:
        //$this->regions = new \MDAO\Regions();
        //$list = $this->regions->listData(array('pager' => 0));
        //if (!empty($list['data']))
        //{
        //    foreach ($list['data'] as $key => $val)
        //    {
        //        echo 'update regions set r_shortname = "' . \MLIB\CUtf8_PY::encode($val['r_name']) . '", r_first = "' . \MLIB\CUtf8_PY::encode($val['r_name'], 'first') . '" where r_id = ' . $val['r_id'] . ';' . "\n";
        //    }
        //}
        //Login:
        //$data = array(
        //    'm_name' => 'test',
        //    'm_pass' => encyptPassword('test'),
        //);
        //$login  = $this->managers_dao->infoData(array('key' => 'm_name', 'val' =>  $data['m_name']));
        //if (!empty($login) && $login['m_pass'] == $data['m_pass'])
        //{
        //    $_SESSION['manager'] = $login;
        //}
        //print_r($_SESSION);
        //------------------------------------------------------------------------------------------------
        //List:
        //$data   = array('pager' => true, 'page' => 1, 'm_status' => -2, 'm_id' => array('type' => 'in', 'value' => '1,2,3'));
        //$data   = array('pager' => true, 'page' => 1, 'm_status' => -2, 'm_id' => array('type' => 'in', 'value' => array(1,2,3,4)));
        //$data   = array('pager' => true, 'page' => 1, 'm_status' => -2, 'm_name' => array('type' => 'like', 'value' => 'admin'), 'm_id' => array('type' => 'in', 'value' => array(1,2,3,4)), 'm_in_time' => array(array('type' => 'ge', 'ge_value' => 1), array('type' => 'le', 'le_value' => 9999999999)));//多个条件并存
        //$data   = array(
        //    'pager' => true,
        //    'page' => 1,
        //    'm_status' => -2,
        //    'm_in_time' => array(
        //        array(
        //            'type' => 'ge',
        //            'ge_value' => 1
        //        ),
        //        array(
        //            'type' => 'le',
        //            'le_value' => 9999999999
        //        )
        //    ),
        //    'in' => array(
        //        'm_id',
        //        '1,2,3',
        //        array(1,2,3,4),
        //    ),
        //    'm_id' => array(
        //        'type' => 'in',
        //        'value' => '1,2,3'
        //    ),
        //    'm_name' => array(
        //        'type' => 'like',
        //        'value' => 'abc',
        //    ),
        //);//2个区间值
        //$data   = array('pager' => true, 'page' => 1, 'm_status' => -2, 'm_in_time' => array('type' => 'ge', 'ge_value' => 2)); //单个区间值
        //$list = $this->managers_dao->listData($data);
        //$list = model('Managers')->gets(array(
        //    'walk'=>array(
        //        'where' => array(
        //            'in' => array(
        //                'm_id', '1,2,3'
        //            )
        //        ),
        //        '_where' => array(
        //            'like' => array(
        //                'm_name', '%abc%'
        //            ),
        //            ''
        //        ),
        //
        //    )
        //));
        //print_r($list);
        //------------------------------------------------------------------------------------------------
        //Info:
        //$info = $this->managers_dao->infoData('1');
        //$info = $this->managers_dao->infoData(array('key' => 'm_name', 'val' =>  $data['m_name']));
        //print_r($info);
        //------------------------------------------------------------------------------------------------
        //Count:
        //$data   = array('m_status' => -2);
        //$counts = $this->managers_dao->countData($data);
        //print_r($counts);
        //------------------------------------------------------------------------------------------------
        //Add:
        //$data = array(
        //    'm_name' => 'test2',
        //    'm_pass' => encyptPassword('test2'),
        //    'm_status' => 0,
        //    'm_in_time' => time(),
        //    'm_inip' => getIp(),
        //    'm_author' => 0,
        //    'mpg_id' => 0,
        //    'm_start_time' => time(),
        //    'm_end_time' => time(),
        //    'm_last_edit_time' => time(),
        //    'm_last_editor' => 0,
        //    'm_last_ip' => getIp(),
        //    );
        //$add  = $this->managers_dao->addData($data);
        //var_dump($add);
        //------------------------------------------------------------------------------------------------
        //Delete:
        //$delete = $this->managers_dao->delData(array('m_id' => array('type' => 'in', 'value' => array(1,2,3)))); //伪删除
        //$delete = $this->managers_dao->delData('7'); //伪删除
        //$delete = $this->managers_dao->delData(array('m_id' => array('type' => 'in', 'value' => array(8,9,20)))); //真删除
        //var_dump($delete);
        //------------------------------------------------------------------------------------------------
        //Update:
        //$data = array(
        //    'm_name' => 'test2',
        //    'm_pass' => encyptPassword('test2'),
        //    'm_status' => 0,
        //    'm_author' => 1,
        //    'mpg_id' => 2,
        //    'm_last_edit_time' => time(),
        //    'm_last_editor' => 2,
        //    'm_last_ip' => getIp(),
        //    );
        //$param = array('m_id' => 7);
        //$update  = $this->managers_dao->updateData($data, $param);
        //var_dump($update);
        //------------------------------------------------------------------------------------------------
        //Sql:
        //$sql = 'show tables';
        //$query = $this->managers_dao->queryData($sql);
        //print_r($query->fetchall());
    }
}