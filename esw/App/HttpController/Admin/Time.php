<?php


namespace App\HttpController\Admin;

use App\Lib\Auth\Aes;
use EasySwoole\Http\AbstractInterface\Controller;

class Time extends Controller
{
    /**
     * 获取服务器时间，用于与客户端同步时间差
     * @return bool|void
     */
    public function index()
    {
        $data = time();
        //var_dump($data);
        return $this->writeJson(200,'ok', $data);
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