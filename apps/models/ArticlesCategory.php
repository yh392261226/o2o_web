<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 16:07:50
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-20 15:59:00
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

        $fields = isset($data['fields'])&&!empty($data['fields'])? $data['fields'] : "*";
        $where = isset($data['where'])&&!empty($data['where'])? $data['where'] : "1";

        return $this->select($fields)->where($where)->fetchAll();
    }


    /**
     * 获取单条数据
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-08-17
     * @param  二维array           $value [description]
     */
    public function infoData($data=array())
    {

        $fields = isset($data['fields'])&&!empty($data['fields'])? $data['fields'] : "*";
        $where = isset($data['where'])&&!empty($data['where'])? $data['where'] : "1";

        return $this->select($fields)->where($where)->fetch();
    }

}