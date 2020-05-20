<?php


namespace App\HttpController\Api;


use App\Lib\Elasticsearch\EsVideo;
use EasySwoole\Http\Message\Status;

class Search extends ApiBase
{
    /**
     * 站内搜索服务API
     * @return array|bool|void
     */
    public function index()
    {
        $keyword = trim($this->params['keyword']);
        if (empty($keyword)){
            return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingDatas(0, [], false));
        }

        $res = (new EsVideo())->searchByName($keyword, $this->params['from'], $this->params['size']);

        $hits = $res['hits']['hits'];
        $total =$res['hits']['total']['value'];

        if (empty($total)){
            return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingDatas(0, [], false));
        }

        foreach ($hits as $hit){
            $source = $hit['_source'];
            $resData = [
                'id' => $hit['_id'],
                'name' => $source['name'],
                'cat_id' => $source['cat_id'],
                'url' => $source['url'],
                'image' => $source['image'],
                'create_time' => '',
                'keyword' => $keyword, // 搜索关键字高亮处理
            ];
        }

        return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingDatas($total, $resData, false));

    }


}