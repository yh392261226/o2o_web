<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 16:06:30
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-16 16:19:52
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
        $data = $this->select();
        return $this->_getChildren($data,$catid,true);
    }
    /*递归处理 输出所有子分类id*/
    public function _getChildren($data,$catid,$isClear=false){
        static $child = array();
        if($isClear){
            $child = array();
        }
        foreach ($data as $k => $v) {
            if($v['parent_id'] == $catid){
                $child[] = $v['id'];
                $this->_getChildren($data,$v['id']);
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
}