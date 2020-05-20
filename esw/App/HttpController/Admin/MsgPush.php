<?php


namespace App\HttpController\Admin;


use App\Lib\Auth\Aes;
use App\Lib\Redis\SocketFdManager;
use App\Model\Admin\Msg;
use EasySwoole\EasySwoole\ServerManager;

class MsgPush extends AdminBase
{
    /**
     * 获取服务器时间，用于与客户端同步时间差
     * @return bool|void
     */
    public function index()
    {
        $data = time();
        //var_dump($data);
        return $this->writeJson(200, 'ok', $data);
    }

    public function sendWsMsg()
    {
        $data = [
            'author' => $this->params['author'],
            'title' => $this->params['title'],
            'content' => $this->params['content'],
        ];
        $server = ServerManager::getInstance()->getSwooleServer();
        // 所有人
        if ($this->params['category_id'] === 1)
        {
            $uids = \App\Model\Admin\User::create()->field('id')->all();
            $uids = json_decode(json_encode($uids), true);
            //var_dump('原始uids: ', $uids);
            $unlineUid = [];       $len = count($uids);
            $fdManager = SocketFdManager::getInstance();
            for ($i=0; $i<$len; $i++) {
                $fd = $fdManager->getFd($uids[$i]['id']);
                if (isset($fd) && !empty($fd) && $server->isEstablished($fd))
                {
                    $server->push($fd, json_encode($data));
                }else{
                    $data['user_id'] = $uids[$i]['id'];
                    $unlineUid[] = $data;
                }
            }
            //var_dump('$unlineUid: ', $unlineUid);
            // 最后调用模型写入离线消息表
            Msg::create()->adds($unlineUid);
        }//仅在线
        elseif ($this->params['category_id'] === 2)
        {
            //var_dump($this->params);
            foreach ($server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($server->isEstablished($fd)) {
                    $server->push($fd, json_encode($data));
                }
            }
        }// 指定用户
        elseif ($this->params['category_id'] === 3)
        {
            if (!empty($uids = $this->params['uids'])) {
                $unlineUid = [];       $len = count($uids);
                $fdManager = SocketFdManager::getInstance();
                for ($i=0; $i<$len; $i++) {
                    $fd = $fdManager->getFd($uids[$i]);
                    if (isset($fd) && !empty($fd) && $server->isEstablished($fd))
                    {
                        $server->push($fd, json_encode($data));
                    }else{
                        $data['user_id'] = $uids[$i];
                        $unlineUid[] = $data;
                    }
                }
                //var_dump('$unlineUid: ', $unlineUid);
                // 最后调用模型写入离线消息表
                Msg::create()->adds($unlineUid);
            }
        }

        $this->writeJson(200, 'ok');
    }

    public function userList() {
        $res = \App\Model\Admin\User::create()->all();
        return $this->writeJson(200, 'ok',$res);
    }

    /**
     * 根据需求变更 重写writeJson方法
     * @param int $statusCode
     * @param null $result
     * @param null $msg
     * @return bool
     */
    public function writeJson($statusCode = 200, $msg = null, $result = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                //'qid' => $this->request()->getHeader('qid'),
                "code" => $statusCode,
                "msg" => $msg,
                "data" => !empty($result) ? Aes::encrypt(json_encode($result)) : null
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }

}