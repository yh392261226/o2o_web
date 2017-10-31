<?php
namespace CLASSES;
use Swoole;
use Swoole\Controller;

class ManageBase extends Swoole\Controller
{
    static $manager_status = 0;
    public $not_validata   = array('Managers_login');
    public $controller_name = '';
    public $view_name = '';
    public $template_ext = '.html';

    public function __construct($swoole)
    {
        parent::__construct($swoole);
        // $this->db->debug = 1;

        /*后台配置文件读取*/
        $web_config = array();
        if (file_exists(WEBPATH . '/configs/web_config.php')){
            require WEBPATH . '/configs/web_config.php';
        }else{
            $dao_Web_config = new \MDAO\Web_config(array('table'=>'web_config'));
            $data = $dao_Web_config ->listData(array('pager'=>false,'fields'=>'wc_name,wc_value','wc_status'=>1,'web_id'=>0));

            $res = array();
            if(!empty($data['data'])){
               foreach ($data['data'] as  $v) {
                $res["{$v['wc_name']}"] = $v['wc_value'];
            }
            }

            file_put_contents(WEBPATH . '/configs/web_config.php','<?php $web_config='.var_export($res,true).'?>');
            if (file_exists(WEBPATH . '/configs/web_config.php')){
                require WEBPATH . '/configs/web_config.php';
            }else{
                $this->exportData(0,array('msg'=>'系统错误请联系管理员'));
            }
        }
        $this->web_config = isset($web_config) ? $web_config : array();

        $this->session->start();

        $this->clearTemplateC(APPPATH . '/manage/cache/templates_c/');

        if (!empty($this->swoole->env['mvc']))
        {
            $this->controller_name = $this->swoole->env['mvc']['controller'];
            $this->view_name = $this->swoole->env['mvc']['view'];
        }
        $this->validataLoginStatus(); //验证登陆状态
        $this->managerAssign();
        // $this->db->debug = true;

    }

    /**
     * @验证是否需要验证登陆状态
     * @return boolean
     */
    protected function validataLoginStatus()
    {
        self::$manager_status = 0;
        if ('' != $this->controller_name && '' != $this->view_name)
        {
            if (!in_array($this->controller_name . '_' . $this->view_name, $this->not_validata))
            {
                //需要验证状态
                if (isset($_SESSION['manager']['m_id']) && $_SESSION['manager']['m_id'] > 0)
                {
                    self::$manager_status = $_SESSION['manager']['m_id'];
                }
                else
                {
                    echo '<script>window.location.href="/Managers/login"</script>';exit;
                }

            }
            return false;
        }
        echo '<script>window.location.href="/Managers/login"</script>';exit;
    }

    /**
     * @模板公共赋值
     */

    public function managerAssign()
    {
        if (defined("MANAGEURL")) {
            $this->tpl->assign("manageurl", STATICPATH);
        }
        if (defined('HOSTURL')) {
            $this->tpl->assign("host_url", HOSTURL);
        }
        $manager_info = !empty($_SESSION['manager']) ? $_SESSION['manager'] : array();
        //regions
        include 'regions.php';
        if (!empty($regions))
        {
            $regions_cache = $regions;
        }
        $this->tpl->assign('regions_cache', $regions_cache);
        $this->tpl->assign('request', $_REQUEST);
        $this->tpl->assign('post', $_POST);
        $this->tpl->assign('get', $_GET);
        $this->tpl->assign('manager_info', $manager_info);
        $this->tpl->assign('menu_list', $this->config['menu']);
    }

    /**
     * 合并基本条件
     * @param array $data
     */
    protected function params($data)
    {
        $param = array();
        if (!empty($data))
        {
            if (is_array($data))
            {

            }
        }
        else
        {
            $param = $data;
        }
        return $param;
    }

    public function mydisplay($name = '')
    {
        if ('' != trim($name))
        {
            $template = $name . $this->template_ext;
        }
        else
        {
            $template = ucfirst($this->controller_name) . '/' . $this->view_name . $this->template_ext;
        }
        $this->tpl->display($template);
    }

    public function myPager($pager)
    {
        if (!empty($pager))
        {
            $pager->set_class('next', 'btn btn-white');
            $pager->set_class('previous', 'btn btn-white');
            $pager->set_class('first', 'btn btn-white');
            $pager->set_class('last', 'btn btn-white');
            $this->tpl->assign('pager', $pager->render());
            $this->tpl->assign('pagesize', '');
            //$this->tpl->assign('pagesize', $pager->set_pagesize());
        }
    }

    /*多文件上传函数*/
    /*
    $form_name:表单中的name名;
    $prefix:生成的文件名的前缀;
    $size:约定图片的最大尺寸;
     */
    protected function uploadAll($form_name,$prefix,$size=array('max_width'=>60,'max_height'=>60,'max_qulitity'=>90))
    {
        /*子目录生成参数*/
        $this->upload->shard_argv = 'Y/m/d';
        /*子目录生成方法，可以使用randomkey，或者date,user*/
        $this->upload->shard_type = 'date';
         //自动压缩图片
        $this->upload->max_width = $size['max_width']; /*约定图片的最大宽度*/
        $this->upload->max_height = $size['max_height']; /*约定图片的最大高度*/
        $this->upload->max_qulitity = $size['max_qulitity']; /*图片压缩的质量*/
        $data = $_FILES;
        $_FILES = array();
        $up_pic = array();
        if(!empty($data[$form_name]['name']))
        {
            foreach($data[$form_name]['name'] as $k=>$f)
            {
                $file_name = $prefix.time().rand(1000,9999);
                if(!empty($data[$form_name]['name'][$k]))
                {
                    $_FILES[$form_name]['name'] = $data[$form_name]['name'][$k];
                    $_FILES[$form_name]['type'] = $data[$form_name]['type'][$k];
                    $_FILES[$form_name]['tmp_name'] = $data[$form_name]['tmp_name'][$k];
                    $_FILES[$form_name]['error'] = $data[$form_name]['error'][$k];
                    $_FILES[$form_name]['size'] = $data[$form_name]['size'][$k];
                    $arr = $this->upload->save($form_name,$file_name);
                    $up_pic[] = $arr['url'];
                }
            }
        }
        return $up_pic;
    }

    private function clearTemplateC($dir)
    {
        $handler = opendir($dir);
        while($file = readdir($handler))
        {
            if ($file != '.' && $file != '..')
            {
                $path = $dir . '/' . $file;
                if (!is_dir($path))
                {
                    unlink($path);
                }
                else
                {
                    $this->clearTemplateC($path);
                }
            }
        }
    }

}