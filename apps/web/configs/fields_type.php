<?php
$fields_type = array(
    //主键类型 =, !=, in, not in
    'primary' => array('solutauthor', 'skills', 'worker', 'against', 'manager', 'pid', 'lasteditor', 'author', 'id', 'province', 'city', 'area', 'status'),
    //时间 <= >= < >
    // 'time' => array('soluttime', 'lastedittime', 'starttime', 'endtime', 'intime', 'usetime'),
    //模糊搜索 like %String%
    'blurred' => array('reason', 'remark', 'truename', 'shortname', 'info', 'title', 'link', 'name', 'value', 'address'),
    //区间值 <= >= < >
    'interval' => array('dissensions', 'duration', 'price', 'workernum', 'total', 'positx', 'posity', 'editamount', 'amountedittimes', 'edittimes', 'ticket', 'envelope', 'overage', 'highopinions', 'lowopinions', 'middleopinions', 'jobsnum', 'workednum', 'credit', 'amount', 'firststart', 'start', 'soluttime', 'lastedittime', 'starttime', 'endtime', 'intime', 'usetime'),
    //特殊类型 后缀为_special
    'special' => array(),
    //混合类型 后缀为_mixed
    'mixed' => array(),
);
return $fields_type;