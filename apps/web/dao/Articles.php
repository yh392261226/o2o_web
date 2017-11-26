<?php
namespace WDAO;

class Articles extends \MDAOBASE\DaoBase
{
	public $articles     = null;
	public $articles_ext = null;

	public function __construct($data)
    {
    	parent::__construct($data);
	}

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
        $data = $this->listData(array('pager'=>false));
        $child_arr = $this->_getChildren($data['data'],$catid,true);
        if($child_arr){
            $ins = implode(',', $child_arr);
            $no_child_arr = $this->listData(array('fields'=>'ac_id,ac_name,ac_pid','ac_id' => array('type' => 'notin', 'value' => $child_arr),'pager'=>false));
        }else{
            $no_child_arr = $data;
        }
        return $this->_getTree($no_child_arr['data'],0,0,true);

    }
     /*递归引用*/
    public function getCategotyChildren($catid)
    {
        $m_ac = model('ArticlesCategory');
        $data = $this->listData();
        return _getChildren($data,$catid,true);

    }

    /*递归引用输出前台树状模型(带level)*/
    public function getTree($catid=0){
        $data = $this->listData(array('fields'=>'ac_id,ac_name,ac_pid','where'=>1,'pager'=>false));
        $data = $data['data'];
        return $this->_getTree($data,$catid,1,true);
    }

    /*递归引用输出前台树状模型(带level)*/
    public function _getTree($data,$pid,$level=1,$isClear=false){
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
    public function getChildTree($catid=0){

        $data = $this->listData(array('fields'=>'ac_id  ,ac_pid,ac_name','where'=>1,'pager'=>false));

        return $this->_getChildTree($data['data'],$catid);
    }
    /*递归引用输出树状模型*/
    public function _getChildTree($data,$pid){

        $tree = array();

        foreach ($data as $k => $v) {
            if($pid == $v['ac_pid']){
                $res = $this->_getChildTree($data,$v['ac_id']);
                if($res){
                   $v['nodes'] =  $res;
                }
                unset($v['ac_pid']);
                $tree[] = $v;
            }

        }
        return $tree;
    }

	/*递归处理 输出所有子分类id*/
    public function _getChildren($data,$catid,$isClear=false)
    {
        static $child = array();
        if($isClear){
            $child = array();
        }
        foreach ($data as $k => $v) {

            if($v['ac_pid'] == $catid){
                $child[] = $v['ac_id'];
                $this->_getChildren($data,$v['ac_id']);
            }
        }
        return $child;
    }


   /*获取文章列表*/
    public function getArticleList($condition=array())
    {
        $a_id_arr = array();
        $page = $condition['page'];
        $pager = empty($page) ? false : true;
        $info = array();
        $info = array(
            'pager'=>$pager,'page'=>$page,
            'fields'=>'a_id,a_title,a_in_time,a_link,a_img,a_top,a_recommend' ,
            );
        $time = time();
        $info['where'] = 'a_status=1 AND (a_start_time <= '.$time.' OR a_start_time = 0 )
        AND (a_end_time >= '.$time.' OR a_end_time = 0)';


       if($condition['ac_id'] > 0)
       {
            /*如果要求有子集*/
            if($condition['son'] > 0){
                /*获取文章信息*/
                $d_ac = new \WDAO\Articles(array('table'=>'Articles_category'));
                $ac_id = $condition['ac_id'];
                $data_ac = $d_ac->listData();
                $child_arr = array();

                $child_arr = getChildren($data_ac['data'],$ac_id,"ac_id","ac_pid",true);
                array_unshift($child_arr,"{$ac_id}");
                $info['in'] = array('ac_id',$child_arr);
            }else{
               /*不要求子集*/
                $info['ac_id'] = $condition['ac_id'];
            }




       }

            /*获取所有文章信息*/
            $a_id_arr = $this->listData($info);

       return $a_id_arr;
    }

}
