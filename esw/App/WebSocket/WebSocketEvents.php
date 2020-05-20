<?php

namespace App\WebSocket;

use App\Lib\StatusCode;
use App\Model\Admin\User;
use EasySwoole\RedisPool\Redis;
use \swoole_server;
use \swoole_websocket_server;
use \swoole_http_request;
use App\Lib\Redis\SocketFdManager;


/**
 * WebSocket Events
 * Class WebSocketEvents
 * @package App\WebSocket
 */
class WebSocketEvents
{
    /**
     * @param swoole_websocket_server $server
     * @param swoole_http_request $request
     * @throws \Throwable
     */
    static function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        $params = $request->get;

        if( empty($params['client-type']) || empty($params['token'])){
            $data = [
                'code' => StatusCode::ERR_ACCESS,
                "msg" => "client-type | token 不能为空"
            ];
            $server->disconnect($request->fd, StatusCode::ERR_ACCESS, json_encode($data));
            return;
        }
        $sid = $params['client-type'] === 'admin' ? 'Session_' : 'Token_' . $params['token'];

        $RedisPool = Redis::defer('redis');

        $user = json_decode( $RedisPool->hGet( $sid, \Yaconf::get('es_conf.admin.LOGIN_TAG')), true );

        //var_dump('onOpen：', $sid);
        if(empty($user) || empty($user['id'])){
            $data = [
                'code' => StatusCode::ERR_ACCESS,
                "msg" => "token 无效"
            ];
            $server->disconnect($request->fd, StatusCode::ERR_ACCESS, json_encode($data));
            return;
        }
        //绑定fd和userId
        SocketFdManager::getInstance()->bind($request->fd, $user['id']);

        // 还需给数据库加入是否在线字段，用于批量推送。。
        User::create()->get($user['id'])->update( ['is_online' => 1] );

        $data = [
            'code' => StatusCode::SUCCESS,
            'msg' => '验证通过，WebSocket连接成功！',
            'fd' => $request->fd
        ];
        $server->push($request->fd, json_encode($data));
    }

    /**
     * @param swoole_server $server
     * @param int $fd
     * @param int $reactorId
     * @throws \Throwable
     */
    static function onClose(\swoole_server $server, int $fd, int $reactorId)
    {
        $userId = SocketFdManager::getInstance()->getUid($fd);
        // 解绑用户信息
        SocketFdManager::getInstance()->delete($fd);
        if (empty($userId) || !isset($userId))
            return;
        // 下线
        User::create()->get($userId)->update( ['is_online' => 0] );
    }

}
