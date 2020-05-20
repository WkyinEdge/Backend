<?php
namespace App\Lib\Elasticsearch;

use EasySwoole\Component\Di;

class EsBase {

    public $esClient = null;

    public function __construct()
    {
        $this->esClient = Di::getInstance()->get('ESH');
    }

    /**
     * @param string $name
     * @param int $from
     * @param int $size
     * @param string $match
     * @return array
     */
    public function searchByName($name, $from =0, $size =10, $match = "match") {

        $name = trim($name);
        if(empty($name)){
            return [];
        }

        $params = [
            'index' => $this->index,
            //'type' => $this->type,
            //'id' => 1,
            'body' => [
                'query' => [
                    $match=> [
                        'name' => $name
                    ],
                ],
                'from' => $from,
                'size' => $size,
            ]
        ];
        //var_dump($params);exit;
        return $this->esClient->search($params);
    }

}
