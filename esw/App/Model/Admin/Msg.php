<?php


namespace App\Model\Admin;

use App\Model\Base;
use EasySwoole\ORM\DbManager;


class Msg extends Base
{
    protected $tableName = 'admin_msg';

    protected $autoTimeStamp = true;
    //protected $createTime = 'create_time';
    //protected $updateTime = 'update_time';

    public function adds(array $data)
    {
        try{
            //开启事务
            DbManager::getInstance()->startTransaction();
            foreach ($data as $k => $v) {
                static::create()->data($v)->save();
            }
        }catch (\Exception $e){
            //回滚事务
            DbManager::getInstance()->rollback();
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }finally{
            //提交事务
            DbManager::getInstance()->commit();
        }
        return true;
    }


}