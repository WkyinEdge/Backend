<?php

namespace SwooleServer\Http\Controller;

use SwooleServer\Http\Model\Base as Model;

class Index extends Base
{
    //测试
    public function index()
    {
        /*$config = new \EasySwoole\Mysqli\Config([
            'host'     => '127.0.0.1',
            'port'     => 3306,
            'user'     => 'wky',
            'password' => 'root',
            'database' => 'es',
            'timeout'       => 5,
            'charset'       => 'utf8mb4',
        ]);
        $client = new \EasySwoole\Mysqli\Client($config);
        //go(function ()use($client) {
            //构建sql
            $client->queryBuilder()->get('admin_user');
            //执行sql
            $res = ($client->execBuilder());
        //});*/

        $redis=\EasySwoole\Pool\Manager::getInstance()->get('redis')->defer();

        $res = $redis->get('name');
        var_dump($res);

        return $this->writeJson(200,'ok', $res);
        //var_dump($config);
        /*
        $res = (new Model)->cs();
        if (!empty($res))
            return $this->writeJson(200,'ok',$res);
        return $this->writeJson(400,'err');
        */
        /*$redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
        //$redis->get('wky');//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程

        $this->response()->end($redis->get('wky'));*/
    }

    public function test()
    {
        //var_dump($this->response());
        return $this->writeJson(200,'ok',["<h1>Test 1</h1>"]);

    }
}