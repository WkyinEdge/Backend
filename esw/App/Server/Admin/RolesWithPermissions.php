<?php


namespace App\Server\Admin;

use App\Server\Base;
use App\Model\Admin\RolesWithPermissions as RolesWithPermissionsModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;


class RolesWithPermissions extends Base
{

    /**
     * 获取角色对应的权限集合
     * @param int $id
     * @return array
     * @throws \Throwable
     */
    public static function getPermissionsByRolesIds($id = 0){
        try{
            $table = \App\Model\Admin\RolesWithPermissions::create();
            $where = !empty($id) ? ['role_id' => $id] : [];
            $withs = $table->getInfoByWhere(true, $where);
            $permissions = [];
            foreach ($withs as $with) {
                $permissions[] = $with->permissions();
            }
        }catch (\Exception $e){
            var_dump('Server\RolesWithPermissions-'.'getPermissionsByRolesIds');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),$e->getCode());
        }

        return $permissions;

    }


    /**
     * 保存角色权限
     * @param $data
     * @throws \Throwable
     */
    public static function saveRolesPermissions($data)
    {
        if (User::check() != intval(\Yaconf::get('es_conf.admin.super_admin_id')))
            throw new \Exception('DEMO演示，禁止危险操作！');

        $roleId = intval($data['role_id']);
        $tmp = $data['permissions_id'];

        try{
            //开启事务
            DbManager::getInstance()->startTransaction();
            RolesWithPermissionsModel::create()->destroy(['role_id' => $roleId]);
            foreach ($tmp as $v) {
                foreach ($v as $v1) {
                    $saveData = ['role_id' => $roleId,'permission_id' => $v1];
                    RolesWithPermissionsModel::create()->data($saveData)->save();
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

        // （先预留）删除用户组下对应用户的缓存权限，缓存菜单
        /**$ids = $this->rolesService->getRoleUsers($roleId);
        if ($ids) {
            flushAnnotationCache('admin-user-permission',$ids);
            flushAnnotationCache('admin-user-menu',$ids);
        }*/

    }

}