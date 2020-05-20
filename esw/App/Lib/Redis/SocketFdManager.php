<?php

namespace App\Lib\Redis;

use EasySwoole\Component\Singleton;
use EasySwoole\RedisPool\Redis;

class SocketFdManager
{
    use Singleton;

    private $fd_with_uid;// fd => userId
    private $uid_with_fd;// userId => fd
    private $outTime;

    /**
     * SocketFdManager constructor.
     * @param string $fd_with_uid  hash中（fd => userId）表的key
     * @param string $uid_with_fd  hash中（userId => fd）表的key
     * @param int $outTime 单位：分钟
     */
    function __construct(string $fd_with_uid = 'fd_with_uid', string $uid_with_fd = 'uid_with_fd',int $outTime = 60 )
    {
        $this->fd_with_uid = $fd_with_uid; // 对应到 redis中 两个hash表的 key值
        $this->uid_with_fd = $uid_with_fd;

        $this->outTime = 60 * $outTime; // 过期时间
    }

    /**
     * 建立绑定关系
     * @param int $fd
     * @param int $userId
     * @return bool
     */
    function bind(int $fd,int $userId)
    {
        $redis = Redis::defer('redis');

        $redis->hSet($this->fd_with_uid, $fd, $userId);
        $redis->expire($this->fd_with_uid,  $this->outTime);    // 调试阶段 设过期时间，完工后就不需要过期了，可永久保存

        $redis->hSet($this->uid_with_fd, $userId, $fd);
        $redis->expire($this->uid_with_fd,  $this->outTime);

        return true;
    }

    function delete(int $fd)
    {
        $redis = Redis::defer('redis');
        $userId = $this->getUid($fd);
        if( !empty($userId) && isset($userId) ){
            $redis->hDel($this->uid_with_fd,$userId);
        }
        $redis->hDel($this->fd_with_uid, $fd);
    }

    /**
     * @param int $fd
     * @return string|null
     */
    function getUid(int $fd):?string
    {
        $ret = Redis::defer('redis')->hGet( $this->fd_with_uid, $fd );
        if($ret){
            return $ret;
        }else{
            return null;
        }
    }

    function getFd(int $userId):?int
    {
        $ret =  Redis::defer('redis')->hGet( $this->uid_with_fd, $userId );
        if($ret){
            return $ret;
        }else{
            return null;
        }
    }

}