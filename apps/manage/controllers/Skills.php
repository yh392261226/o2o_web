<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-09-08 14:30:07
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-09-09 13:44:45
 */
namespace App\Controller;

class Skills extends \CLASSES\ManageBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }
    public function index()
    {
        $dao_skills = new \MDAO\Skills(array('table'=>'skills'));
        $condition = array();
        $condition['search_condition'] = isset($_GET['search_condition'])&&!empty($_GET['search_condition']) ? $_GET['search_condition'] : "";
        $condition['page'] = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;


        if(!empty($condition['search_condition'])){
            $condition['s_name'] = array('type'=>'like','value'=>$condition['search_condition']);
        }
        unset($condition['search_condition']);
        /*获取技能数组*/
        $arr_skill = $dao_skills ->listData($condition);

        if(isset($arr_skill['pager'])){
            $this->myPager($arr_skill['pager']);
        }


        if(!empty($condition['search_condition'])){
            $this->tpl->assign("search_condition",$condition['search_condition']);
        }
        $this->tpl->assign('data',$arr_skill['data']);
        $this->tpl->display("Skills/index.html");
    }
    /*加载投诉技能添加模板*/
    public function skillsAdd()
    {
        $this->tpl->display("Skills/skillsAdd.html");
    }
    /*处理投诉添加数据*/
    public function doSkillsAdd()
    {
        $jump = "/skills/skillsAdd";
        $dao_skills = new \MDAO\Skills(array('table'=>'skills'));
        if(!isset($_POST['s_name']) || !isset($_POST['s_name'])){
            msg("请填写技能名称", $status = 0, $jump);
        }else{
            /*判断技能名是否存在*/
            $res = $dao_skills ->infoData(array('key'=>'s_name','val'=>$_POST['s_name'],'fields'=>'s_id'));

            if(isset($res['s_id'])){
                msg("技能名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['s_name'] = trim($_POST['s_name']);
        $data['s_status'] = isset($_POST['s_status'])?intval($_POST['s_status']):0;
        $data['s_info'] = isset($_POST['s_info'])?trim($_POST['s_info']):'';
        $data['s_desc'] = isset($_POST['s_desc'])?trim($_POST['s_desc']):'';

        $res = $dao_skills ->addData($data);
        if($res){
            msg("技能添加成功", $status = 1, $jump);
        }else{
            msg("技能添加失败!", $status = 0, $jump);
        }

    }




    /*技能修改*/
    public function skillsEdit()
    {
        $jump = "/skills/skillsList";
        $s_id = isset($_GET['s_id']) ? intval($_GET['s_id']) : 0;

        if($s_id == 0){
            msg("参数错误!", $status = 0, $jump);
        }



        $dao_skills = new \MDAO\Skills(array('table'=>'skills'));


        $self_data = $dao_skills ->infoData(array('key'=>'s_id','val'=>$s_id));



        $this->tpl->assign("self_data",$self_data);
        $this->tpl->display("Skills/skillsEdit.html");
    }


     /*处理技能修改数据*/
    public function dosKillsEdit()
    {

       $jump = "/skills/index";
        $dao_skills = new \MDAO\Skills(array('table'=>'skills'));

        if(!isset($_POST['s_name']) || empty($_POST['s_name']) || !isset($_POST['s_id']) || empty($_POST['s_id'])){
            msg("参数不足", $status = 0, $jump);
        }else{
            /*判断技能名是否存在*/
            $res = $dao_skills ->infoData(array('key'=>'s_name','val'=>$_POST['s_name'],'fields'=>'s_id'));
            $s_id = intval($_POST['s_id']);


            if(isset($res['s_id']) && intval($res['s_id']) > 0 && $res['s_id'] != $s_id){
                msg("技能名已经存在!", $status = 0, $jump);
            }
        }

        $data = array();
        $data['s_name'] = trim($_POST['s_name']);
        $data['s_status'] = isset($_POST['s_status'])?intval($_POST['s_status']):0;
        $data['s_info'] = isset($_POST['s_info'])?trim($_POST['s_info']):'';
        $data['s_desc'] = isset($_POST['s_desc'])?trim($_POST['s_desc']):'';

        $res = $dao_skills -> updateData($data,array('s_id' => $s_id));
        if($res){
            msg("技能修改成功", $status = 1, $jump);
        }else{
            msg("技能修改失败!", $status = 0, $jump);
        }

    }

    public function changeStatus()
    {
       if(isset($_GET['s_id'],$_GET['s_status'])&&!empty($_GET['s_id'])){
            $s_id = intval($_GET['s_id']);
            $s_status = intval($_GET['s_status']);

            $dao_skills = new \MDAO\Skills(array('table'=>'skills'));
            $res = $dao_skills ->updateData(array('s_status'=>$s_status),array('s_id'=>$s_id));

            if($res){
                echo 1;
            }

       }else{
         echo 0;
       }
    }
}