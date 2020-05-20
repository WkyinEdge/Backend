<?php

/**
 *  composer自动加载原理：引入了autoload.php文件，就可以使用 use引入已经在composer.json中注册过根路径的类库，例如：（"autoload":{"psr-4":{"App\\":"App/"}}）其中
 * App就是注册过的根路径名，自己编写的类库想通过composer加载，必须去json文件中声明路径，然后composer update一下，就可以通过namespace + use 的方式注册或加载了

require './vendor/autoload.php';
use App\Lib\Redis\Redis;
var_dump(Redis::getInstance());
*/

 /*
 $http = new swoole_http_server('0.0.0.0',8811);

 $http->set(
     [
         'work_num' => 16,
         'enable_static_handler' =>true,
         'document_root' => '/www/admin/192.168.253.128_80/wwwroot/',
     ]
 );
 $http->on('request', function ($request, $response) {
     $response->end(1);
 });

 $http->start();
*/

/*
Co\run(function () {
    $server = new Co\Http\Server("0.0.0.0", 8811, false);
    $server->handle('/', function ($request, $response) {
        //$response->end("<h1>Index</h1>");

        // 屏蔽Google浏览器发的favicon.ico请求
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            return $response->end();
        }
        $response->header("charset","utf-8",true);

        go(function () use ($response) {

            $time = time();
            \Co::sleep(2);
            //sleep(2);
            //var_dump("测试1用时：".strval(time()-$time).'秒');
            //$response->write('测试1用时：'.strval(time()-$time).'秒');

        });
        //$response->write('外围');

    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
*/

                    /* 多进程式：协程Http服务端貌似没作用。。。官方说的是多进程+协程TCP */
// 多进程管理模块
$workerNum = 2;
$pool = new \Swoole\Process\Pool($workerNum);

// 让每个OnWorkerStart回调都自动创建一个协程
$pool->set(['enable_coroutine' => true]);
$pool->on("workerStart", function ($pool, $id) {

    // 每个进程都监听8811端口
    $server = new \Co\Http\Server("0.0.0.0", 9501, false, true);

    // 收到15信号关闭服务
    \Swoole\Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    // 接收到新的连接请求
    $server->handle('/', function ($request, $response) {
        //$response->end("<h1>Index</h1>");

        // 屏蔽Google浏览器发的favicon.ico请求
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            return $response->end();
        }
        $response->header("charset","utf-8",true);

        var_dump($request);

/*
        go(function () use ($response) {

            $time = time();
            \Co::sleep(2);
            //sleep(2);
            //var_dump("测试1用时：".strval(time()-$time).'秒');
            //$response->write('测试1用时：'.strval(time()-$time).'秒');

        });
        //go(function () use ($response){
            //$time = time();
            //\Co::sleep(2);
            //var_dump("测试2用时：".strval(time()-$time).'秒');
            //$response->write('测试2用时：'.strval(time()-$time).'秒');
        //});
        //$response->write('外围');
*/
    });

    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });

    // 开始监听端口
    $server->start();
});
$pool->start();
