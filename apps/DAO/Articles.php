<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 16:06:30
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-19 16:55:18
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

    /*获取所有除了自己子集的分类树*/
    public function getTreeExceptChild($catid)
    {
        $m_ac = model('ArticlesCategory');
        $data = $m_ac->infoDatas();
        $child_arr = getChildren($data,$catid,"ac_id","ac_pid",true);

        if($child_arr){
            $ins = implode(',', $child_arr);
            $no_child_arr = $m_ac->infoDatas(array('fields'=>'ac_id,ac_name,ac_pid',"where"=>"`ac_id` NOT IN ({$ins})"));
        }else{
            $no_child_arr = $data;
        }

        return $this->_getTree($no_child_arr,0,0,true);

    }
     /*递归引用*/
    public function getCategotyChildren($catid)
    {
        $m_ac = model('ArticlesCategory');
        $data = $m_ac->infoDatas();
        return getChildren($data,$catid,"ac_id","ac_pid",true);

    }

    /*递归引用输出前台树状模型(带level)*/
    public function getTree($catid=0){
        $m_ac = model('ArticlesCategory');
        $data = $m_ac->infoDatas(array('fields'=>'ac_id,ac_name,ac_pid','where' => '1'));
        return $this->_getTree($data,$catid,0,true);
    }

    /*递归引用输出前台树状模型(带level)*/
    public function _getTree($data,$pid,$level=0,$isClear=false){
        static $tree = array();
        if($isClear){
            $tree = array();
        }
        foreach ($data as $k => $v) {
            if($pid == $v['ac_pid']){
                $v['level'] = $level;
                $tree[] = $v;
                $this->_getTree($data,$v['ac_id'],$level+1);
            }
        }
        return $tree;
    }

    /*递归引用输出树状模型*/
    public function getChildTree($catid){
        $m_ac = model('ArticlesCategory');
        $data = $m_ac->infoDatas(array('fields'=>'ac_id as tags ,ac_name as text,ac_pid','where' => '1'));
        return $this->_getChildTree($data,$catid);
    }
    /*递归引用输出树状模型*/
    public function _getChildTree($data,$pid){

        $tree = array();

        foreach ($data as $k => $v) {
            if($pid == $v['ac_pid']){
                $res = $this->_getChildTree($data,$v['tags']);
                if($res){
                   $v['nodes'] =  $res;
                }
                unset($v['ac_pid']);
                $tree[] = $v;
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

/**
 * 判断分类下是否存在子分类
 */
   public function catChild($ac_id)
   {
        $data['fields'] = 'ac_id';
        $data['where'] = array('ac_pid'=>$ac_id);

        return model('ArticlesCategory')->infoDatas($data);
   }

/**
 * 判断分类下是否存在文章
 */
   public function artChild($ac_id)
   {
        $data['fields'] = 'a_id';
        $data['where'] = array('ac_id'=>$ac_id);

        return model('Articles')->infoDatas($data);
   }

/*删除文章分类*/
   public function delCategory($ac_id)
   {
        $data['val'] = $ac_id;
        $data['key'] = 'ac_id';
        return model('ArticlesCategory')->delData($data);
   }

/*查找分类详情*/
public function getCategory($ac_id)
{
    $data['fields'] = '*';
    $data['where'] = array('ac_id'=>$ac_id);

    return model('ArticlesCategory')->infoDatas($data);
}






























}