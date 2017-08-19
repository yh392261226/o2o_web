<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 16:07:50
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-19 15:49:19
 */
namespace App\Model;


class ArticlesCategory extends \CLASSES\ModelBase
{
    /*文章表*/
    public $table   = 'articles_category';
    public $primary = "ac_id";


    /**
     * 获取多条数据不分页
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-08-17
     * @param  二维array           $value [description]
     */
    public function infoDatas($data=array())
    {
        $this->setdatas($data);
        $paras = $this->paras;
        return $this->select($paras['fields'])->where($paras['where'])->fetchAll();
    }

    /**
     * @param array $data
     * @return array
     * @author Me
     * @desc 多条详情
     */
    // public function listDatas($data = array())
    // {
    //     $result= $this->gets($data);
    //     return $result;
    // }
}