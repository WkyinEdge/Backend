<?php
namespace App\Lib\Elasticsearch;

use EasySwoole\Component\CoroutineSingleTon;
use Elasticsearch\ClientBuilder;

class EsClient {
    use CoroutineSingleTon;

    public $esClient = null;

    private function __construct(){
        try{
            $esConfig = \Yaconf::get('es_conf.elasticsearch');
            $this->esClient = ClientBuilder::create()->setHosts([$esConfig['host'] . ':' . $esConfig['port']])->build();
        } catch (\Exception $e){
            //throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
            throw new \Exception(__METHOD__ . ' -- ' . 'Elasticsearch服务异常');
        }

        if ($this->esClient === false) {
            throw new \Exception(__METHOD__ . ' -- ' . 'Elasticsearch链接失败');
        }
    }

    public function __call($name, $arguments)
    {
        return $this->esClient->$name(...$arguments);
    }
}
