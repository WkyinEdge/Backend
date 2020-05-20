<?php


namespace App\Server\Admin;

use App\Model\Api\Video as VideoModel;
use App\Server\Base;

class Video extends Base
{
    /**
     * 提供后台视频列表筛选功能列表
     * @param $params
     * @return array
     * @throws \Throwable
     */
    public static function getVideoList( $params ) {
        // category_id /title/start_time/end_time // page相关
        try {
            $order = ['create_time', 'DESC'];
            $where = [];
            if (!empty($params['category_id'])) {
                $where['category_id'] = $params['category_id'];
            }
            if (!empty($params['title'])) {
                $where['title'] = [ '%' . $params['title'] . '%','like' ];
            }
            if (!empty($params['start_time']) && !empty($params['end_time'])) {
                $where['create_time'] =
                    [
                        [strtotime($params['start_time']), strtotime($params['end_time'])],
                        'between'
                    ];
            }

            $res = VideoModel::create()->getPage($params['current_page'], $params['page_size'], $where, $order);

        } catch (\Exception $e) {
            var_dump('Server\Video-' . 'getVideoList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        $data = [];
        if (!empty($res)) {
            $data = [
                'pages' => $res['pageInfo'],
                'list' => $res['data']
            ];
        }
        return $data;
    }


    public static function saveVideo($params) {

        $saveData = [];
        if (!empty($params['title']))
            $saveData['title'] = $params['title'];
        if (!empty($params['category_id']))
            $saveData['category_id'] = $params['category_id'];
        if (!empty($params['url']))
            $saveData['url'] = $params['url'];
        if (!empty($params['image']))
            $saveData['image'] = $params['image'];
        if (!empty($params['content']))
            $saveData['content'] = $params['content'];
        if (!empty($params['uploader_name']))
            $saveData['uploader_name'] = $params['uploader_name'];

        if (isset($params['uploader_id']))
            $saveData['uploader_id'] = $params['uploader_id'];
        if ( isset($params['status']) )
            $saveData['status'] = $params['status'];
        if ( isset($params['is_top']) )
            $saveData['is_top'] = $params['is_top'];
        if ( isset($params['is_recommend']) )
            $saveData['is_recommend'] = $params['is_recommend'];

        if (!empty($params['id']))
            $saveData['id'] = $params['id'];

        return self::save($saveData, false, 'Video');
    }

}