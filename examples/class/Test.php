<?php
namespace BL;

use Swoole\Protocol\SOAServer;

class Test
{
    static function test1($str = 'empty')
    {
        //var_dump(SOAServer::getClientEnv());
        //var_dump(SOAServer::getRequestHeader());
        return "hello-soa-finish: $str";
    }

    static function hello()
    {
        return array('key1' => 'A', 'key2' => 'B');
    }
}