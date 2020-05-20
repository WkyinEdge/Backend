<?php


namespace App\Lib\Redis;

use App\Lib\StatusCode;
use App\Lib\Utils;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\RedisPool\Redis;

class RedisSession
{
    use CoroutineSingleTon;

    /**
     * 缓存key值前缀：后台管理统一用 'SESSION_' 标识，前台应用统一用 'token_'
     * @var string
     */
    private $keyPrefix;
    private $sid;

    /**
     * RedisSession constructor.
     * @param bool $isAdmin
     */
    private function __construct( bool $isAdmin = true )
    {
        if ( $isAdmin )
            $this->keyPrefix = 'Session_';
        else
            $this->keyPrefix = 'Token_';
    }
    /**
     * 获取一个sessionId | 设置当前会话sessionId
     * @param string $sid
     * @param int $outTime
     * @return string
     * @throws \Exception
     */
    public function SessionId(string $sid = '')
    {
        if (!empty($sid)){
            $this->sid = $sid;
        }else{
            // 生成一个唯一的sessionId
            $this->sid = Utils::getOnlyKey();
            return $this->sid;
        }
        /*$redis = Redis::defer('redis');
        $redis->set($this->keyPrefix . $sessionId, '');
        // 设置未登录客户端 过期时间
        $redis->expire($this->keyPrefix . $sessionId, $outTime);*/
    }
    /**
     * 获取当前会话ID
     * @return mixed
     * @throws \Exception
     */
    public function getSid() {
        if (empty($this->sid))
        {
            throw new \Exception('获取Sid错误，未建立有效会话', StatusCode::ERR_INVALID_TOKEN);
        }
        return $this->keyPrefix . $this->sid;
    }

    /**
     * 保存session信息
     * @param $field
     * @param $value
     * @param int $outTime
     * @return bool
     * @throws \Exception
     */
    public function write($field, $value, $outTime = 3600)
    {
        $redis = Redis::defer('redis');

        $redis->hSet($this->getSid(), $field, json_encode($value));
        // 已登录客户端 过期时间
        $redis->expire($this->getSid(),  $outTime);

        return true;
    }

    /**
     * 读取session信息
     * @param $field
     * @throws \Exception
     * @return mixed
     */
    public function read($field)
    {
        return json_decode( Redis::defer('redis')->hGet( $this->getSid(), $field), true );
    }

    /**
     * 主动销毁session会话
     * @param $token
     * @param bool $noBind  是否解绑userId => token ?
     * @throws \Exception
     * @return bool
     */
    public function destory( $token = '',bool $noBind = true)
    {
        $redis = Redis::defer('redis');

        if ( !empty($noBind) ) {
            $userInfo = $this->read(\Yaconf::get('es_conf.admin.LOGIN_TAG'));
            if ( !empty($userInfo) ){
                $redis->del($this->keyPrefix . $userInfo['id'] );
            }
        }
        $redis->del( !empty($token) ? $token : $this->getSid() );

        return true;
    }

    /**
     * 做userId => token 的反向关联（用来实现多设备登录踢下线）
     * @param $userId
     * @param $token
     * @param int $outTime
     * @return bool
     * @throws \Exception
     */
    public function bind($userId, $token, $outTime = 3600)
    {
        $redis = Redis::defer('redis');
        $redis->set($this->keyPrefix . $userId, $this->keyPrefix . $token);
        $redis->expire($this->keyPrefix . $userId, $outTime);
        return true;
    }

    /**
     * 判断指定用户是否已登录
     * @param $userId
     * @return bool
     */
    public function isExist($userId)
    {
        $token = Redis::defer('redis')->get( $this->keyPrefix . $userId );
        if (empty($token))
            return false;
        return $token;
    }


}