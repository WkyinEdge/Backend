<?php


namespace App\HttpController;


use App\Lib\Auth\Aes;
use App\Lib\Auth\IAuth;
use App\Lib\ClassArr;
use App\Lib\Elasticsearch\EsVideo;
use App\Lib\Redis\RedisSession;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Session\Session;
use Elasticsearch\ClientBuilder;


class Index extends Base
{
    //测试
    public function index()
    {
        /*$file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));*/

        /**
         * ESH测试
        $param = $this->request()->getRequestParam('name');
        $res = (new EsVideo)->searchByName($param);
        return $this->writeJson(200,$res,'OK');
        */

         // 协程测试：最终得出 ESW 并不支持协程服务端，默认应该是异步风格
        /*go(function (){
            $time = time();
            \Co::sleep(2);
            var_dump("测试2用时：".strval(time()-$time).'秒');
            $this->response()->write('测试2用时：'.strval(time()-$time).'秒');
        });*/
        //var_dump(json_encode($this->response()));

        //var_dump('协程外围');

        //$this->writeJson(200,'ok',\Yaconf::get('es_cs'));
        /*$data = [
            'did' => '12345dg',
            'version' => 1,
            'time' => time(),
        ];
        $str = 'IZKL4SGo+pZa5gDpDqsVUGmszxYHgBwgPaI3Ol+uU/lit+z2Do00U8zXSMe/BdWS';*/
        // col9j6cqegAKiiey3IrXWo2zCRGHw8vogniwQZab0fgIVnKDb7Rin03dOqY2qLWP
        //var_dump( $str = IAuth::setSign($data) );//exit;
        //var_dump( Aes::decrypt($str) );//exit;
       //try{
            //$a = ClassArr::adminClassStat()['aaa'];
            //var_dump($a);
        //var_dump(__NAMESPACE__);
        //var_dump(__CLASS__);
        //var_dump(__FUNCTION__ );
        //var_dump(__METHOD__ );
            //throw  new \Exception(__METHOD__ . ' -- ' . '错了');
        //}catch (\Exception $e){
            //var_dump('dd');
            //throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        //}
            /*$redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
            $redis->get('wky');//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程*/

        var_dump($_POST['ip']);

        //var_dump($this->request()->decryptParams);

    }

    public function sendWsMsg()
    {

        $fd = $this->request()->getRequestParam('fd');
        $text = $this->request()->getRequestParam('text');
        $data = [
            'msg' => $text
        ];

        $server = ServerManager::getInstance()->getSwooleServer();
        //foreach ($server->connections as $fd) {
            // 需要先判断是否是正确的websocket连接，否则有可能会push失败
            if ($server->isEstablished($fd)) {
                $server->push($fd, json_encode($data));
            }
        //}
        $this->writeJson(200, 'ok');
    }


    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}