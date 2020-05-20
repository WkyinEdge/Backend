<?php

namespace SwooleServer\WebSocket\Model;

class Base
{
    //protected $tableName = 'admin_user';

    public function cs() {

        //var_dump('cs');

        //\Co\run(function () {
            $swoole_mysql = new \Swoole\Coroutine\MySQL();
            $swoole_mysql->connect([
                'host'     => '127.0.0.1',
                'port'     => 3306,
                'user'     => 'wky',
                'password' => 'root',
                'database' => 'es',
            ]);
            $res = $swoole_mysql->query('select * from admin_user where id=1');
            return $res;
            //var_dump($res);
        //});
    }

}