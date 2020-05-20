<?php
//declare(strict_types=1);
/**
 * 自己实现一个swoole http服务器，可以自定义控制器，自定义路由...
 */
namespace SwooleServer\WebSocket;

use EasySwoole\Pool\Config;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Pool\Manager;
use SwooleServer\Common\Pool\RedisPool;

class WebSocketServer{

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


        //Co\run(function () {
            $server = new \Co\Http\Server($host, $port, false);
            $server->handle('/websocket', function ($request, $ws) {
                $ws->upgrade();
                while (true) {
                    $frame = $ws->recv();
                    if ($frame === false) {
                        echo "error : " . swoole_last_error() . "\n";
                        break;
                    } else if ($frame == '') {
                        break;
                    } else {
                        if ($frame->data == "close") {
                            $ws->close();
                            return;
                        }
                        $ws->push("Hello {$frame->data}!");
                        $ws->push("How are you, {$frame->data}?");
                    }
                }
            });

            $server->handle('/', function ($request, $response) {
                $response->end(<<<HTML
                        <h1>Swoole WebSocket Server</h1>
                        <script>
                            var wsServer = 'ws://192.168.253.128:8811/websocket';
                            var websocket = new WebSocket(wsServer);
                            websocket.onopen = function (evt) {
                                console.log("Connected to WebSocket server.");
                                websocket.send('hello');
                            };

                            websocket.onclose = function (evt) {
                                console.log("Disconnected");
                            };

                            websocket.onmessage = function (evt) {
                                console.log('Retrieved data from server: ' + evt.data);
                            };

                            websocket.onerror = function (evt, e) {
                                console.log('Error occured: ' + evt.data);
                            };
                        </script>
HTML
                );
            });

            $server->start();
        //});

    }

    public function getHttpServer() {
        return $this->httpServer;
    }

}
