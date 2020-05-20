<?php

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Di;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Socket\Dispatcher;
use App\WebSocket\WebSocketParser;
use App\System\ServerRegisters;
use App\Lib\Cache\ApiCache;
use App\WebSocket\WebSocketEvents;


class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        // 一键协程化所有IO客户端
        \Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);//真正的hook所有类型，包括CURL

        // 注册 MySql-ORM 协程连接池
        ServerRegisters::initOrmPool();
        // 注册 RedisPool 协程连接池（只能在协程环境下使用）
        ServerRegisters::initRedisPool();
        // 加载项目目录的配置文件 （已弃用，改Yaconf）
        // self::loadConf(EASYSWOOLE_ROOT . '/Config');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        /**
         * **************** websocket控制器 | 事件服务注册 **********************
         */
        // 创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $conf->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new Dispatcher($conf);
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
            if (empty($frame->data)) return; // 心跳检测请求直接跳过
            $dispatch->dispatch($server, $frame->data, $frame);
        });
        // 注册事件服务
        $register->add(EventRegister::onOpen, [WebSocketEvents::class, 'onOpen']);
        $register->add(EventRegister::onClose, [WebSocketEvents::class, 'onClose']);
        /**
         * ********************************* end **********************************
         */
        //Di::getInstance()->set('REDIS',Redis::getInstance());
        //Di::getInstance()->set('ESH',EsClient::getInstance());

        // 协程 ESH
        $config = new \EasySwoole\ElasticSearch\Config([
            'host' => \Yaconf::get('es_elasticsearch.host'),
            'port' => \Yaconf::get('es_elasticsearch.port')
        ]);
        $elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);
        Di::getInstance()->set('ESH', $elasticsearch);


        // 注册定时 静态化任务
        //Cache::getInstance()->addTask(CrontabCache::class);

        // 改用定时器的方法执行定时 静态化任务
        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            //如何避免定时器因为进程重启而丢失  //例如在第一个进程 添加一个10秒的定时器
            if ($workerId == 0) {
                Timer::getInstance()->loop(1000 * 10 * 6 * 60 * 24, function () {
                    ApiCache::setIndexVideo();
                });
            }
        });

        /** 框架启动时全局注册 */

        // 注册 FastCache 单机高性能内存型缓存
        ServerRegisters::fastCacheRegister();
        // 注册 TP 模板引擎
        //ServerRegisters::initTemplate();
        // 用Redis--List实现简单消息队列消费者
        //ServerRegisters::simpleQueue();
        // FastCache 消费者进程注册
        //ServerRegisters::FCQueue();
        // Redis 消费者进程注册
        ServerRegisters::redisQueue();
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // 全局跨域、处理 OPTIONS 请求
        if ( !ServerRegisters::setHeader($request, $response) ) return false;
        // Seesion 预处理
        ServerRegisters::sessionHandler($request, $response);
        // sign校验
        if ( !ServerRegisters::signAuth($request, $response) ) return false;
        // 参数解密
        ServerRegisters::paramsDecrypt($request, $response);

        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }

    // 模拟测试生产者 在实际使用中 可在任意区域投递生产者
    /*
       {
       Timer::getInstance()->loop(1000*5, function () {

        // 简单队列 生产者
        // \EasySwoole\RedisPool\Redis::getInstance()->get('redis')->defer()
        //    ->rPush(\Yaconf::get('es_conf.queue.smQueueKey'), date('i:s',time()));

        // FastCache队列 生产者
        //$job = new Job();
        //$job->setData(['测试' => date('i:s',time())]); // 任意类型数据
        //$job->setQueue(\Yaconf::get('es_conf.queue.fc_queue'));
        //$jobId = Cache::getInstance()->putJob($job);
        //var_dump($jobId);


        // Redis 生产者
        //$redisPool = \EasySwoole\RedisPool\Redis::getInstance()->get('redis');
        //$driver = new \EasySwoole\Queue\Driver\Redis($redisPool,\Yaconf::get('es_conf.queue.redis_queue'));
        //$queue = new \EasySwoole\Queue\Queue($driver);
        //go(function ()use($queue) {

        //    $job = new \EasySwoole\Queue\Job();
        //    $data = date('i:s',time());
        //    $job->setJobData($data);
        //    $id = $queue->producer()->push($job);
        //    echo ('create1 id :'.$id.PHP_EOL);
        //});

    });
    }
    */
}