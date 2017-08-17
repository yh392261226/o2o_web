<?php
define('STATICPATH', WEBPATH . '/../../static');
define('STATICURL', 'http://devstatic.gangjianwang.com');
$upload = array(
    'base_dir' => STATICPATH.'/manager',
    'base_url' => STATICURL,
);
var_dump($upload);
return $upload;