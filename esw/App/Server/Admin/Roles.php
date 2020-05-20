<?php


namespace App\Server\Admin;

use App\Server\Base;
use App\Model\Admin\Roles as RolesModel;
use EasySwoole\Http\Message\Status;


class Roles extends Base
{

    /**
     * 获取角色对应权限list
     * @param $id 角色id
     * @return array
     * @throws \Throwable
     */
    public static function getRolePermissions($id)
    {
        try {
            if (!$id) {
                var_dump('Server\Roles-' . 'getRolePermissions');
                throw new \Exception('参数错误');
            }

            $list = RolesWithPermissions::getPermissionsByRolesIds($id);
            $list = Permissions::handelPermissionsGroup($list);

            $allList = Permissions::getPermissionsTreeList();
            foreach ($allList as $v) {
                if (!isset($list[$v['id']])) {
                    $list[$v['id']] = [];
                }
            }
            $data = [
                'permissions_list' => $allList,
                'role_permissions' => $list,
            ];
        } catch (\Exception $e) {
            var_dump('Server\Roles-' . 'getRolePermissions2');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(), $e->getCode());
        }
        return $data;
    }

    /**
     * 保存角色，构造数据，防止注入
     * 不接收数据库字段以外数据
     * @param $inputData
     * @return bool|int
     * @throws \Throwable
     */
    public static function saveRoles($params)
    {
        $data = [];
        $data['display_name'] = $params['display_name'];
        //$data['name'] = $params['name'];
        $data['description'] = !empty($params['description']) ? $params['description'] : '';

        $data['id'] = !empty($params['id']) ? $params['id'] : '';

        // 构造查重条件
        $where = '';
        if (!empty($params['name']) && $params['name'] != 'null') {
            $data['name'] = $params['name'];
            $where .= "name='" . $data['name'] . "'";
        }

        return self::save($data, false, 'Roles', $where);
    }

    /**
     * 查询角色列表
     */
    public static function getList()
    {
        try {
            $list = RolesModel::create()->getInfoByWhere(true);
        } catch (\Exception $e) {
            var_dump('Server\Roles-' . 'getList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $list ?: [];
    }


}