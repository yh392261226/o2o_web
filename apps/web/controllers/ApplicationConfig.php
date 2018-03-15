<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-10-23 13:31:55
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-10-23 13:36:41
 */
namespace App\Controller;

class ApplicationConfig extends \CLASSES\WebBase
{
    public function __construct($swoole)
    {
        parent::__construct($swoole);
    }

    /*前台配置文件接口*/
    public function getAppConfig()
    {
        $application_config = array();
        if (file_exists(WEBPATH . '/configs/application_config.php')){
            require WEBPATH . '/configs/application_config.php';
        }else{
            $dao_application_config = new \WDAO\Users(array('table'=>'application_config'));
            $data = $dao_application_config ->listData(array('pager'=>false,'fields'=>'ac_name,ac_value','ac_status'=>1));

            $res = array();
            foreach ($data['data'] as  $v) {
                $res["{$v['ac_name']}"] = $v['ac_value'];
            }
            file_put_contents(WEBPATH . '/configs/application_config.php','<?php $application_config='.var_export($res,true).'?>');
            if (file_exists(WEBPATH . '/configs/application_config.php')){
                require WEBPATH . '/configs/application_config.php';
            }else{
                $this->exportData(0,array('msg'=>'系统错误请联系管理员'));
            }
        }

        $this->exportData( array('data' => $application_config),1);

    }

}