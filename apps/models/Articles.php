<?php
/**
 * @Author: Zhaoyu
 * @Date:   2017-08-14 16:07:50
 * @Last Modified by:   Zhaoyu
 * @Last Modified time: 2017-08-18 16:22:22
 */
namespace App\Model;


class Articles extends \CLASSES\ModelBase
{
    /*文章表*/
    public $table   = 'articles';
    public $primary = "a_id";

}