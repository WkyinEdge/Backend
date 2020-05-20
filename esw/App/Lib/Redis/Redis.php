<?php
namespace App\Lib\Redis;

use EasySwoole\Component\CoroutineSingleTon;

class Redis {
    use CoroutineSingleTon;

    public $redis;

    private function __construct(){
        if (!extension_loaded('redis')){
            throw new \Exception('redis.so文件不存在');
        }
        try{
            //配置文件写法
            //$redisConfig = Config::getInstance()->getConf('redis');
            //Yaconf 配置插件写法
            $redisConfig = \Yaconf::get('es_conf.redis');
            //var_dump($redisConfig);
            //var_dump($redisConfig1);
            $this->redis = new \Redis();
            $result = $this->redis->connect($redisConfig['host'],$redisConfig['port'],$redisConfig['time_out']);
        } catch (\Exception $e){
            //throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
            throw new \Exception(__METHOD__ . ' -- ' . 'redis服务异常');
        }

        if ($result === false) {
            throw new \Exception(__METHOD__ . ' -- ' . 'redis链接失败');
        }
    }

    /**
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key, $value, $time = 0){
        if (empty($key)){
            return '';
        }
        if (is_array($value)){
            $value = json_encode($value);
        }
        if (!$time){
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
    }

    /**
     * @param $key
     * @return bool|string
     */
    public function get($key) {
        if (empty($key)) {
            return '';
        }
        return $this->redis->get($key);
    }

    /**
     * 取出第一个元素
     * @param $key
     * @return string
     */
    public function lPop($key) {
        if(empty($key)) {
            return '';
        }
        return $this->redis->lPop($key);
    }

    /**
     * 在末尾添加元素
     * @param $key
     * @param $value
     * @return bool|int|string
     */
    public function rPush($key, $value) {
        if(empty($key)) {
            return '';
        }
        return $this->redis->rPush($key,$value);
    }

    /**
     * 对有序集合(sorted set)中指定成员的分数加上$number个增量
     * @param $key 集合标识
     * @param $number 增量
     * @param $member 该集合下的成员名
     * @return bool|float
     */
    /*public function zincrBy($key, $number, $member){

        if(empty($key) || empty($member)){
            return false;
        }
        return $this->redis->zIncrBy($key, $number, $member);

    }*/

    /**
     * @param $key
     * @param $start
     * @param $stop
     * @param $type
     * @return array|bool
     */
    /*public function zrevrange($key, $start, $stop, $type){
        if(empty($key)){
            return false;
        }
        //var_dump($this->redis->zRevRange($key, $start, $stop, true));exit;
        return $this->redis->zRevRange($key, $start, $stop, $type);
    }*/

    /**
     * 专门用来测试redis函数的方法
     */
    public function csDos(){

        $this->redis;

    }

    /**
     *  当对象在其他地方调用 类中不存在的方法时，自动调用__call  实现调用底层redis相关的方法
     * ... 可变长度操作符, 只能接收数组
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->redis->$name(...$arguments);
    }
}
