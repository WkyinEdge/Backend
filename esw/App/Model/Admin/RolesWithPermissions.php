<?php


namespace App\Model\Admin;

use App\Model\Base;
use EasySwoole\ORM\DbManager;
use EasySwoole\Mysqli\QueryBuilder;

class RolesWithPermissions extends Base
{
    protected $tableName = 'admin_roles_permissions';

    protected $autoTimeStamp = true;
    //protected $createTime = 'create_time';
    //protected $updateTime = 'update_time';

    /*public function roles()
    {
        return $this->hasOne(Roles::class, null, 'role_id','id');
    }*/

    public function permissions()
    {
        return $this->hasOne(Permissions::class, null, 'permission_id','id');
    }


    /**
     * 保存角色权限，因为协程的原因会导致多条数据同时存入，导致主键重复报错
     * @param $data
     * @throws \Throwable
     */
    /*public function saveRolesPermissions($data)
    {
        $roleId = intval($data['role_id']);
        $tmp = $data['permissions_id'];

        try{
            //开启事务
            DbManager::getInstance()->startTransaction();
            $this->destroy(['role_id' => $roleId], true);

            //$builder = new QueryBuilder();

            foreach ($tmp as $v) {
                foreach ($v as $v1) {
                    $saveData = ['role_id' => $roleId,'permission_id' => $v1];
                    $this->data($saveData)->save();
                    //$builder->insert('admin_roles_permissions', $saveData);
                    //var_dump($v1);
                }
            }
        }catch (\Exception $e){
            //回滚事务
            DbManager::getInstance()->rollback();
            var_dump('Server\RolesWithPermissions-'.'saveRolesPermissions');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }finally{
            //提交事务
            DbManager::getInstance()->commit();
        }

    }*/

}