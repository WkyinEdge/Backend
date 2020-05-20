<?php


namespace App\Lib\Process;

use EasySwoole\Component\Di;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RedisPool\Redis;
use Swoole\Process;

/**
 * 简单消费者
 * Class SimpleConsumer
 * @package App\Lib\Process
 */
class SimpleConsumer extends AbstractProcess
{
    private $isRun = false;

    public function run($arg)
    {
        $this->addTick(500,
            //go(
                function (){
                if (!$this->isRun){
                    $this->isRun = true;
                    $redisPool = Redis::getInstance()->get('redis');
                    $redis = $redisPool->getObj();
                    while (true){
                        try{
                            // 获取任务
                            $task = $redis->lPop(\Yaconf::get('es_conf.queue.smQueueKey'));
                            if ($task){
                                go(function () use ($task){
                                    // 发邮件 推送服务 。。。这里以写log日至为例
                                    // Logger::getInstance()->log($this->getProcessName().'---'.$task);
                                    var_dump($this->getProcessName().'--'.$task);
                                });
                            }else{
                                break;
                            }
                        } catch (\Throwable $throwable){
                            break;
                        }
                    }
                    // 回收入池 也可用defer()方法获取redis，会自动回收
                    $redisPool->recycleObj($redis);
                    $this->isRun = false;
                }
                //var_dump($this->getProcessName());
            }
          //);
        );

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
    }

}