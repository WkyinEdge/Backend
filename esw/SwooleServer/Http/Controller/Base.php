<?php

namespace SwooleServer\Http\Controller;

class Base
{
    /**
     * 路由配置可单独抽离出来，放入配置文件中或者php文件中，然后提供一个接口 给swoole 请求入口读取路由即可
     * @return array
     */
    static function route()
    {
        $routeArr = [
            '/' => [
                'Controller' => 'Index',
                'action' => 'index'
            ],
            '/test' => [
                'Controller' => 'Index',
                'action' => 'test'
            ],
            '/reload' => [
                'Controller' => 'Index',
                'action' => 'reload'
            ],
        ];
        return $routeArr;
    }

    private $request;
    private $response;
    private $server;
    // private $pool;

    public function __construct($request, $response, $server)
    {
        $this->request = $request;
        $this->response = $response;
        $this->server = $server;
    }

    /**
     * 只读属性
     * @return mixed
     */
    public function request(){return $this->request;}

    public function response(){return $this->response;}

    public function server() {return $this->server;}

    public function reload() {
        $this->server()->reload();
        $this->writeJson(200, '服务器重启成功');
    }

    /**
     * API响应Json数据格式
     * @param int $statusCode
     * @param null $result
     * @param null $message
     * @return bool
     */
    public function writeJson($statusCode = 200, $message = null, $result = null)
    {
        //if (!$this->response()->isEndResponse()) {
        $data = Array(
            "code" => $statusCode,
            "msg" => $message,
            "data" => $result
        );
        $this->response()->status($statusCode, $statusCode);
        $this->response()->header('Content-type', 'application/json;charset=utf-8');
        $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return true;
        //} else {
        //return false;
        //}
    }

}