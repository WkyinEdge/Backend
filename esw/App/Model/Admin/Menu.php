<?php


namespace App\Model\Admin;

use App\Model\Base;
use EasySwoole\Mysqli\QueryBuilder;


class Menu extends Base
{
    protected $tableName = 'admin_menu';

    protected $autoTimeStamp = true;
    //protected $createTime = 'create_time';
    //protected $updateTime = 'update_time';



    //func(function ($builder){
    //                $builder->raw("select * from menu order by ord asc");
    //                return true;
    //            });
    /**
     * @param array $where
     * @param array $order
     * @return array
     * @throws \Throwable
     */
    /*public function getList($where=[], $order = ['ord', 'ASC']) {

        try{
            $model =self::create();

            // Orm自带封装的 order 函数就是个bug，所以暂时只能用MySQL闭包处理，且不好抽离封装
            $res = $model->where($where)->all(function(QueryBuilder $queryBuilder) use ($order){

                if (is_array($order) && !empty($order) && isset($order[0]))
                    $queryBuilder->orderBy($order[0],$order[1] ?? 'ASC');
            });

            //var_dump($model->lastQuery()->getLastQuery());//exit;
        }catch (\Exception $e){
            // 写日志、报警...
            var_dump('Model\Menu-'.'getList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }

        return $res ??  [];


    }*/


}