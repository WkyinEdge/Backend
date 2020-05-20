<?php


namespace App\Lib\Process;

use EasySwoole\RedisPool\Redis;
use EasySwoole\Queue\Driver\Redis as Driver;
use EasySwoole\Queue\Queue;
use EasySwoole\Component\Process\AbstractProcess;

use EasySwoole\EasySwoole\Logger;
use Swoole\Process;


/**
 * Redis 消费者进程 适合复杂场景|| 支持分布式 中大型后端适用
 * Class SimpleConsumer
 * @package App\Lib\Process
 */
class RedisConsumer extends AbstractProcess
{
    private $isRun = false;

    public function run($arg)
    {
        go(function (){

            $redisPool = Redis::getInstance()->get('redis');
            $driver = new Driver($redisPool, \Yaconf::get('es_conf.queue.redis_queue'));
            $queue =  new Queue($driver);

            $queue->consumer()->listen(function (\EasySwoole\Queue\Job $job)
            {   //业务逻辑

                // 发邮件 推送服务 。。。这里以写log日至为例
                // Logger::getInstance()->log($this->getProcessName().'---'.$task);
                var_dump($this->getProcessName().'--'.strval($job->getJobData()));

            },0.01,0.1);
        });
        //new \SwooleServer\WebSocket\WebSocketServer();

    }

    protected function onPipeReadable(Process $process)
    {
        /*
         * 该回调可选
         * 当有主进程对子进程发送消息的时候，会触发的回调，触发后，务必使用
         * $process->read()来读取消息
         */
    }

    protected function onShutDown()
    {
        /*
         * 该回调可选
         * 当该进程退出的时候，会执行该回调
         */
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        /*
         * 该回调可选
         * 当该进程出现异常的时候，会执行该回调
         */
        var_dump($throwable->getMessage());
    }

}