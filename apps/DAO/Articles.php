<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 16:06:30
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-17 17:20:42
 */
namespace DAO;

/**
 * Class UsManagerer
 * example: $user = new App\DAO\Manager();  $user->get();
 * @package App\DAO
 */
class Articles
{
    /**
     * 获取文章分类数组
     * @author zhaoyu
     * @e-mail zhaoyu8292@qq.com
     * @date   2017-08-14
     * @return [type]            [description]
     */
     /*递归引用*/
    public function getChildren($catid){
        $m_ac = model('ArticlesCategory');
        $data = $m_ac->infoDatas();
        return $this->_getChildren($data,$catid,true);
    }
    /*递归处理 输出所有子分类id*/
    public function _getChildren($data,$catid,$isClear=false){
        static $child = array();
        if($isClear){
            $child = array();
        }
        foreach ($data as $k => $v) {
            var_dump($v);
            if($v['ac_pid'] == $catid){
                $child[] = $v['ac_id'];
                $this->_getChildren($data,$v['ac_id']);
            }
        }
        return $child;
    }

    /*递归引用输出树状模型*/
    public function getTree($catid){
        $data = $this->select();
        return $this->_getTree($data,$catid,0,true);
    }

    /*递归引用输出树状模型*/
    public function _getTree($data,$pid,$level=0,$isClear=false){
        static $tree = array();
        if($isClear){
            $tree = array();
        }
        foreach ($data as $k => $v) {
            if($pid == $v['parent_id']){
                $v['level'] = $level;
                $tree[] = $v;
                $this->_getTree($data,$v['id'],$level+1);
            }
        }
        return $tree;
    }

   /*添加文章分类*/
   public function saveArticeCat($data)
   {
       return model('ArticlesCategory')->saveData($data);
   }

   /*通过分类名查找分类id*/
   public function getIdByName($name)
   {

        $data['fields'] = 'ac_id';
        $data['where'] = array('ac_name'=>$name);

        return model('ArticlesCategory')->infoDatas($data);
   }
}