<?php

namespace App\System;

use App\Lib\Auth\Aes;
use App\Lib\Auth\IAuth;
use App\Lib\Process\RedisConsumer;
use App\Lib\Process\FastCacheConsumer;
use App\Lib\Process\SimpleConsumer;
use App\Lib\Redis\RedisSession;
use App\Lib\StatusCode;
use App\Lib\Utils;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\CacheProcessConfig;
use EasySwoole\FastCache\SyncData;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Template\Render;
use EasySwoole\Utility\File;

/**
 * 全局服务注册封装
 * Class ServerRegisters
 * @package App\System
 */
class ServerRegisters
{
    /**
     * 跨域 、OPTIONS 请求预处理
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public static function setHeader(Request $request, Response $response)
    {
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, tid, client-type, sign, token');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(StatusCode::SUCCESS);
            return false;
        }
        return true;
    }

    /**
     * Seesion 预处理
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     */
    public static function sessionHandler(Request $request, Response $response)
    {
        $clientType = $request->getHeader('client-type');

        if ( !empty($clientType[0]) && $clientType[0] === 'admin' )
        {   // 如果是后台管理系统，用 cookie + session 方式做登录验证

            // 获取 cookie 如果为空，创建 sessionId并设置给 cookie | 如果已存在，给session设置当前会话sessionId
            $sid = $request->getCookieParams('esw_session');

            // 改用redis存储session
            if (empty($sid)) {
                // 可以不设置cookie 因为后台管理页面必须登录才能访问，登录成功后，会自动刷新session和cookie
                //$sid = RedisSession::getInstance()->SessionId(); //$response->setCookie('esw_session', $sid);
            } else {
                RedisSession::getInstance()->SessionId($sid);
            }
        }else { // 如果是其他客户端，统一都用 token方式
            // 初始化 RedisSession单例对象，传入 false设置为 token模式（默认不传参为 cookie模式，cookie模式不用初始化）
            RedisSession::getInstance(false);
            // 获取token 如果为空，就是游客身份，可以给token也可以不给 | 如果存在 给当前会话sessionId 设置为 token
            $token = $request->getHeader('tid');
            if ( !empty($token[0]) )
            {
                RedisSession::getInstance()->SessionId($token[0]);
            }
        }
    }


    /**
     * sign校验
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public static function signAuth(Request $request, Response $response)
    {
        $res = IAuth::checkSignPass($request->getHeaders());
        if ($res['code'] !== StatusCode::SUCCESS)
        {
            Utils::writeJson($response, $res['code'], $res['msg']);
            return false;
        }
        return true;
    }

    /**
     * 解密客户端请求参数
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     */
    public static function paramsDecrypt(Request $request, Response $response)
    {
        $encrypt = $request->getRequestParam('encrypt');
        if (!empty($encrypt)) {
            try {
                $request->decryptParams = json_decode(Aes::decrypt($encrypt), true);
                //var_dump($request->decryptParams );
            } catch (\Throwable $e) {
                Utils::writeJson($response, StatusCode::ERR_NOT_ACCESS, '参数错误');
            }
        } else {
            $request->decryptParams = [];
        }
    }

    /**
     * MySql-ORM 协程连接池注册
     */
    public static function initOrmPool()
    {

        $config = new Config();
        $config->setHost(\Yaconf::get('es_conf.mysql.host'));
        $config->setPort(\Yaconf::get('es_conf.mysql.port'));
        $config->setUser(\Yaconf::get('es_conf.mysql.user'));
        $config->setPassword(\Yaconf::get('es_conf.mysql.pwd'));
        $config->setDatabase(\Yaconf::get('es_conf.mysql.database'));

        DbManager::getInstance()->addConnection(new Connection($config));
    }

    /**
     * Redis 协程连接池注册（只能在协程环境下使用）
     */
    public static function initRedisPool()
    {

        // 单机环境注册
        $redisconf = new \EasySwoole\Redis\Config\RedisConfig();
        $redisconf->setHost('127.0.0.1');
        $redisconf->setPort(6379);
        $redisPoolConfig = \EasySwoole\RedisPool\Redis::getInstance()->register('redis', $redisconf);
        /**
         * 集群注册
         * $redisClusterPoolConfig = \EasySwoole\RedisPool\Redis::getInstance()->register('redisCluster',new \EasySwoole\Redis\Config\RedisClusterConfig(
         * [
         * ['172.16.253.156', 9001],
         * ['172.16.253.156', 9002],
         * ['172.16.253.156', 9003],
         * ['172.16.253.156', 9004],
         * ],[
         * 'auth' => '',
         * 'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_PHP
         * ]
         * ));*/

        // 配置连接池连接数
        $redisPoolConfig->setMinObjectNum(3);
        $redisPoolConfig->setMaxObjectNum(10);
        $redisPoolConfig->setAutoPing(10);//设置自动ping的间隔

    }

    /**
     * 注册 TP 模板引擎
     */
    public static function initTemplate()
    {

        Render::getInstance()->getConfig()->setRender(new ThinkTemplate());
        Render::getInstance()->getConfig()->setTempDir(EASYSWOOLE_TEMP_DIR);
        Render::getInstance()->attachServer(ServerManager::getInstance()->getSwooleServer());
    }

    /**
     * （消费者）基于 Redis 的消息队列子进程 支持分布式 适合中大型企业后端
     */
    public static function redisQueue()
    {
        $processConfig = new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName('redisQueue');//设置进程名称
        $processConfig->setEnableCoroutine(true);//是否自动开启协程，开启后 子进程中自动部署go环境
        Manager::getInstance()->addProcess(new RedisConsumer($processConfig));
    }

    /**
     * （消费者）基于 FastCache 的消息队列子进程 功能强大且同时具备内存型缓存 适合单服务器的小型项目 在不集群的情况下完全取代代替Redis
     */
    public static function FCQueue()
    {
        $processConfig = new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName('FCQueue');//设置进程名称
        $processConfig->setEnableCoroutine(true);//是否自动开启协程
        Manager::getInstance()->addProcess(new FastCacheConsumer($processConfig));
    }

    /**
     * （消费者）基于Redis--List自定义的简单消息队列支持协程，因为list本身只存储单条字符串，适合处理信息量小，操作简单的服务，比如写日志
     */
    public static function simpleQueue()
    {

        $processConfig = new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName('simpleQueue');//设置进程名称
        //$processConfig->setProcessGroup('Test');//设置进程组
        //$processConfig->setArg(['a'=>123]);//传参
        //$processConfig->setRedirectStdinStdout(false);//是否重定向标准io
        //$processConfig->setPipeType($processConfig::PIPE_TYPE_SOCK_DGRAM);//设置管道类型
        $processConfig->setEnableCoroutine(true);//是否自动开启协程
        //$processConfig->setMaxExitWaitTime(3);//最大退出等待时间

        Manager::getInstance()->addProcess(new SimpleConsumer($processConfig));
        // 任务量大时，可通过循环创建多个进程，同时消费管道信息
        /*
           for ($i=0;$i<3;$i++){
               // 保证进程名不重复，可注释上方单个进程名设置
               $processConfig->setProcessName("wky_consumer_process_{$i}");
               Manager::getInstance()->addProcess(new Consumer($processConfig));
           }
        */
    }

    /**
     * FastCache 相关注册封装
     * 安装FastCache要求http：1.5.1 所以被迫移除"easyswoole/http": "^1.6"
     */
    public static function fastCacheRegister()
    {
        // 每隔5秒将数据存回文件
        Cache::getInstance()->setTickInterval(5 * 1000);//设置定时频率
        Cache::getInstance()->setOnTick(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
            $data = [
                'data' => $SyncData->getArray(),
                'queue' => $SyncData->getQueueArray(),
                'ttl' => $SyncData->getTtlKeys(),
                // queue支持（队列）
                'jobIds' => $SyncData->getJobIds(),
                'readyJob' => $SyncData->getReadyJob(),
                'reserveJob' => $SyncData->getReserveJob(),
                'delayJob' => $SyncData->getDelayJob(),
                'buryJob' => $SyncData->getBuryJob(),
            ];
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            File::createFile($path, serialize($data));
        });

        // 启动时将存回的文件重新写入
        Cache::getInstance()->setOnStart(function (CacheProcessConfig $cacheProcessConfig) {
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            if (is_file($path)) {
                $data = unserialize(file_get_contents($path));
                $syncData = new SyncData();
                $syncData->setArray($data['data']);
                $syncData->setQueueArray($data['queue']);
                $syncData->setTtlKeys(($data['ttl']));
                // queue支持
                $syncData->setJobIds($data['jobIds']);
                $syncData->setReadyJob($data['readyJob']);
                $syncData->setReserveJob($data['reserveJob']);
                $syncData->setDelayJob($data['delayJob']);
                $syncData->setBuryJob($data['buryJob']);
                return $syncData;
            }
        });

        // 在守护进程时,php easyswoole stop 时会调用,落地数据
        Cache::getInstance()->setOnShutdown(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
            $data = [
                'data' => $SyncData->getArray(),
                'queue' => $SyncData->getQueueArray(),
                'ttl' => $SyncData->getTtlKeys(),
                // queue支持
                'jobIds' => $SyncData->getJobIds(),
                'readyJob' => $SyncData->getReadyJob(),
                'reserveJob' => $SyncData->getReserveJob(),
                'delayJob' => $SyncData->getDelayJob(),
                'buryJob' => $SyncData->getBuryJob(),
            ];
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            File::createFile($path, serialize($data));
        });

        Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }

    /**
     * 加载自定义配置文件（已弃用，改用Yaconf）
     * @param $ConfPath
     */
    public static function loadConf($ConfPath)
    {
        $Conf = \EasySwoole\EasySwoole\Config::getInstance();
        $files = File::scanDirectory($ConfPath);
        //var_dump($files);
        foreach ($files['files'] as $file) {
            //var_dump($file);
            $data = require_once $file;
            $Conf->setConf(strtolower(basename($file, '.php')), (array)$data);
        }
    }

}