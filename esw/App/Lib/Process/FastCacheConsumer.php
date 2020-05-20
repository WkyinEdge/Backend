<?php


namespace App\Lib\Process;

use EasySwoole\FastCache\Cache;
use EasySwoole\Component\Process\AbstractProcess;

use EasySwoole\EasySwoole\Logger;


/**
 * FastCache 消费者
 * Class SimpleConsumer
 * @package App\Lib\Process
 */
class FastCacheConsumer extends AbstractProcess
{
    private $isRun = false;

    public function run($arg)
    {
        $this->addTick(500,
            function (){
                if (!$this->isRun){

                    $this->isRun = true;
                    $fcQueue = Cache::getInstance();

                    while (true){
                        try{

                            // 获取任务 Job对象或者null
                            $job = $fcQueue->getJob(\Yaconf::get('es_conf.queue.fc_queue'));

                            if ($job === null){
                                break;
                            }else{
                                // 执行业务逻辑
                                go(function () use ($job){

                                    // 发邮件 推送服务 。。。这里以写log日至为例
                                    // Logger::getInstance()->log($this->getProcessName().'---'.$task);
                                    var_dump($this->getProcessName().'--'.implode($job->getData()));

                                    // 执行完了要删除或者重发，否则超时会自动重发
                                    Cache::getInstance()->deleteJob($job);
                                });

                            }
                        } catch (\Throwable $throwable){
                            break;
                        }
                    }
                    $this->isRun = false;
                }
                //var_dump($this->getProcessName());
            }
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