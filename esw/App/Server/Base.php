<?php


namespace App\Server;

use App\Lib\ClassArr;
use EasySwoole\ORM\DbManager;

class Base
{

    /**
     * （封装）删除信息，存在子节点不允许删除
     * @param $id
     * @param string $classKey    需要反射处理的key
     * @return bool|int
     * @throws \Throwable
     */
    public static function deleteInfo($id, $classKey)
    {
        try{
            if (empty($classKey)){
                throw new \Exception('$classKey不能为空');
            }
            // 反射处理 获取 model 类
            $modelClass = ClassArr::initClass($classKey, ClassArr::adminClassStat(), [],false);

            $res = $modelClass::create()->getInfoByWhere(true, ['parent_id' => $id] );

            $count = count($res);
            if ($count){
                var_dump('Server\Base-'.'deleteInfo');
                throw new \Exception('存在子节点不允许删除');
            }
            $num = $modelClass::create()->delete($id);

        }catch (\Exception $e) {
            var_dump('Server\Base-'.'deleteInfo2');
            throw new \Exception( $e->getMessage(),$e->getCode());

        }
        return $num;
    }


    /**
     * （封装）拖动排序相关
     * @param $ids
     * @param string $classKey    需要反射处理的key
     * @return bool
     * @throws \Throwable
     */
    public static function dragSort($ids, $classKey) {
        try{
            if (empty($classKey)){
                throw new \Exception('$classKey不能为空');
            }
            // 反射处理 获取 model 类
            $modelClass = ClassArr::initClass($classKey, ClassArr::adminClassStat(), [],false);

            //开启事务
            DbManager::getInstance()->startTransaction();
            $order = 0; // 排序计数器
            //var_dump($ids);
            foreach ($ids as $v) {
                $modelClass::create()->updateById($v, ['ord' => $order]);
                $order++;
            }
        }catch (\Exception $e){
            //回滚事务
            DbManager::getInstance()->rollback();
            var_dump('Server\Base-'.'dragSort');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());

        }finally{
            //提交事务
            DbManager::getInstance()->commit();
        }
        return true;
    }

    /**
     * （封装）添加 / 更新
     * @param $data
     * @param $hasOrder // 补充排名
     * @param $classKey
     * @param string $where 查重条件
     * @return bool|int|string
     * @throws \Exception
     */
    public static function save($data, $hasOrder, $classKey, string $where = '')
    {
        try {
            // 反射处理 获取 model 类
            $modelClass = ClassArr::initClass($classKey, ClassArr::adminClassStat(), [],false);
            // 添加逻辑
            if ( !isset($data['id']) || !$data['id'] )
            {
                // 对唯一字段查重处理
                if ( !empty($where) )
                {
                    $obj = $modelClass::create()->where($where);
                    $res = $obj->get();
                    //var_dump($obj->lastQuery()->getLastQuery()); // 打印sql语句
                    if (!empty($res)) {
                        throw new \Exception('输入的信息已存在，请核查后重新输入');
                    }
                }
                // 补充排名
                if ($hasOrder)
                {
                    $row = $modelClass::create()->getInfoByWhere(true, ['parent_id' => $data['parent_id']]);
                    $data['ord'] = count($row);
                }
                $id = $modelClass::create()->add($data);
                // 返回bool 不转换就是主键id
                //$res = !empty($res) ? true : false;
                //var_dump($res);
                return $id ?: false;
            }
            else {
                // 修改逻辑
                $id = intval($data['id']) ?? '';
                // 对唯一字段查重处理, 排除自身
                if ( !empty($where) )
                {
                    //var_dump($where);
                    $obj = $modelClass::create()->where($where)->where(['id' => [$id, '!=']]);
                    $res = $obj->get();
                    //var_dump($obj->lastQuery()->getLastQuery()); // 打印sql语句
                    if (!empty($res)) {
                        throw new \Exception('输入的信息已存在，请核查后重新输入');
                    }
                }
                $res = $modelClass::create()->updateById($id, $data);
                // 接口要求返回id
                //var_dump($res);
                return !empty($res) ? $id : false;
            }
        } catch (\Exception $e) {
            var_dump('Server\Base-'.'save');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }

    }


}