<?php


namespace App\Model\Api;

use App\Model\Base;

class Video extends Base
{
    protected $tableName = 'video';

    protected $autoTimeStamp = true;
    //protected $createTime = 'create_time';
    //protected $updateTime = 'update_time';

    /**
     * 获取器
     * $value mixed 是原值
     * $data  array 是当前model所有的值
     */
    /*protected function getStatusAttr($value, $data)
    {
        $status = [-1=>'已删除',0=>'待审核',1=>'已发布'];
        return $status[$value];
    }*/

    /**
     * 修改器
     * $value mixed 是原值
     * $data  array 是当前model所有的值
     */
    /*protected function setStatusAttr($value, $data)
    {
        return $value === '已发布' ? 1 : $value === '待审核' ? 0 : $value === '已删除' ? -1 : 0;
    }

    protected function getIsTopAttr($value, $data)
    {
        $isTop = [0=>'未置顶',1=>'已置顶'];
        return $isTop[$value];
    }

    protected function setIsTopAttr($value, $data)
    {
        return $value === '已置顶' ? 1 : 0;
    }

    protected function getIsRecommendAttr($value, $data)
    {
        $isRecommend = [0=>'未推荐',1=>'已推荐'];
        return $isRecommend[$value];
    }

    protected function setIsRecommendAttr($value, $data)
    {
        return $value === '已推荐' ? 1 : 0;
    }*/

    protected function getCreateTimeAttr($value, $data)
    {

        return date('y/m/d H:i', $value);
    }

    /**
     * 获取视频列表的分页数据
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param array $condition where条件
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getPageInfo($page=1, $limit=10,$condition = []) {

        $queryObj = $this;
        if(!empty($condition['cat_id'])){
            $queryObj->where('cat_id', $condition['cat_id']);
        }
        $table = $queryObj->where('status', 1)
                ->order('id','desc')
                ->page($page,$limit);

        $lists = $table->all();

        $res = $table->lastQueryResult();

        var_dump($table->lastQuery()->getLastQuery()); // 打印sql语句

        /* 获取总条数类似于count(*) */
        $total = $res->getTotalCount();
        /* 求总页数 */
        $totalPages = ceil($total / $limit);
        //var_dump($totalPages);
        $data = [
            'totalPages' => $totalPages,
            'page_size' => $limit,
            'count' => $total,
            'lists' => $lists
        ];
        return $data;
    }

    /**
     * 用于定时任务定时获取前1000条数据
     * @param int $limit
     * @param array $condition
     * @return array|bool|\EasySwoole\ORM\Db\Cursor
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getVideoCachedata($condition = [], $limit=1000) {
        //var_dump($condition);exit;
        $queryObj = $this;
        if(!empty($condition['cat_id'])){
            $queryObj->where('cat_id', $condition['cat_id']);
        }
        $table = $queryObj->where('status', 1)
            ->order('id','desc')
            ->limit($limit);

        $lists = $table->all();

        //var_dump($table->lastQuery()->getLastQuery());//exit; // 打印sql语句

        return $lists;
    }


}