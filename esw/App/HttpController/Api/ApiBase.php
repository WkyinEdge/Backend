<?php


namespace App\HttpController\Api;

use App\HttpController\Base;

/* Api 模块下的基类*/
class ApiBase extends Base
{

    /**
     *  根据需求重写 getParams()
     */
    public function getParams()
    {
        //$params = $this->request()->decryptParams;
        $params = $this->request()->getRequestParam();

        // 静态化缓存数据分页相关
        $params['page'] = !empty($params['page']) ? intval($params['page']) : 1;
        $params['size'] = !empty($params['size']) ? intval($params['size']) : 1;
        $params['from'] = ($params['page'] - 1) * $params['size'];

        // 分类id
        $params['cat_id'] = !empty($params['cat_id']) ? intval($params['cat_id']) : 0;

        // 排行榜周期参数
        $params['period'] = !empty($params['period']) ? intval($params['period']) : 0;

        $this->params = $params;
    }

    /**
     * 本地Json数据分页处理
     * @param $count 总条数
     * @param $data 本地数据
     * @param $isSplice 是否需要切割
     * @return array
     */
    public function getPagingDatas($count, $data, $isSplice = true) {
        // 总条数 除以 每页显示条数 求总页数
        $totalPage = ceil($count / $this->params['size']);
        $maxPageSize = \Yaconf::get('es_base.maxPageSize');
        if ($totalPage > $maxPageSize){
            $totalPage = $maxPageSize;
        }

        $data = $data ?? [];

        if ($isSplice){
            $data = array_splice($data, $this->params['from'], $this->params['size']);
        }


        return [
            'total_page' => $totalPage,
            'page_size' => $this->params['page'],
            //'size' => $this->params['size'],
            'count' => intval($count),
            'lists' => $data
        ];
    }

}