<?php
//declare(strict_types=1);
/**
 * 自己实现一个swoole http服务器，可以自定义控制器，自定义路由...
 */
namespace SwooleServer\Http;

use EasySwoole\Pool\Config;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Pool\Manager;
use SwooleServer\Common\Pool\RedisPool;

class HttpServer{

    private $httpServer;
    private $route = [];

    /**
     * HttpServer constructor.
     * @param string $host 监听地址
     * @param int $port 监听端口
     * @param int $worker_num worker 进程数
     * @param bool $enable_coroutine 是否开启全自动协程环境
     */
    function __construct(string $host = '0.0.0.0', int $port = 8811, int $worker_num = 8, bool $enable_coroutine = true)
    {

        // 注册 redis 连接池
        $config = new Config();
        $redisConfig = new RedisConfig();
        Manager::getInstance()->register(new RedisPool($config,$redisConfig),'redis');


        $this->httpServer = new \Swoole\Http\Server($host, $port);

        $routeArr = require_once 'route.php';
        foreach ($routeArr as $route) $this->route += $route;

        $this->httpServer->set([
            'reactor_num' => 16,
            'worker_num' => $worker_num,
            'enable_coroutine' => $enable_coroutine,
        ]);
        // $this->httpServer->on('Start', function (\Swoole\Server $server) {});

        $this->httpServer->on('request', function ($request, $response)
        {
            /** 屏蔽Google浏览器发的favicon.ico请求 */
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') return $response->end();

            /** 以下是都是 自定义路由 、 自定义控制器...可根据需求更改
             */
            $pathInfo =  !empty($request->server['request_uri']) ? $request->server['request_uri'] : '/';
            //var_dump(explode('/', trim($request->server['request_uri'], '/')));

            if ( empty($this->route[$pathInfo]) ) return $response->end('页面不存在');

            /** 获取路由 */
            $route = $this->route[$pathInfo];

            /** 取得对应控制器类名和方法名 */
            $class = 'SwooleServer\\Http\\Controller\\' . $route['Controller'];
            $action = $route['action'];

            /** 交由控制器处理 */
            return ( new $class($request, $response, $this->httpServer) )->$action();
        });

        $this->httpServer->start();

    }

    public function getHttpServer() {
        return $this->httpServer;
    }

}
