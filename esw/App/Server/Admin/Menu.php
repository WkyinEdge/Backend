<?php


namespace App\Server\Admin;

use App\Lib\Common\Common;
use App\Lib\Utils;
use App\Model\Admin\Menu as MenuModel;
use App\Server\Base;


class Menu extends Base
{
    /**
     * 超级管理员用户组id
     */
    //const ROOT_ROLE_ID = 1;

    /**
     * 获取用户权限对应的路由树
     * @param $userId
     * @return array
     * @throws \Throwable
     */
    public static function getUserMenuList($userId)
    {
        try {
            // 获取用户对应权限名
            $userPermissions = \App\Server\Admin\Permissions::getUserAllPermissions($userId);
            // 获取菜单表
            $menuList = MenuModel::create()->getInfoByWhere(true, [], ['ord', 'ASC']);

            $menuList = json_decode(json_encode($menuList), true);

            //给所有url不为空的起始位加上 '/'
            foreach ($menuList as $k => &$v) {
                if (!empty($v['url'])) {
                    $v['url'] = '/' . ltrim($v['url'], '/');
                }
                if ($v['permission_id'] && $v['permission_name'] && !in_array($v['permission_name'], $userPermissions)) {
                    unset($menuList[$k]);
                }
            }
            unset($v);
            // 递归运算 得出用嵌套数组形式保存的树结构
            $tree = Utils::handleTreeList($menuList);
            // 删除没有子节点的 menu
            foreach ($tree as $k1 => $v1) {
                if (!(isset($v1['children']) && $v1['children'])) {
                    unset($tree[$k1]);
                }
            }
        } catch (\Exception $e) {
            var_dump('Server\Menu-' . 'getUserMenuList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(), $e->getCode());
        }
        return $tree;

    }

    /**
     * 获取所有菜单路由表
     * @return array
     * @throws \Throwable
     */
    public static function getMenuTree( $status = [] )
    {
        try {
            $list = MenuModel::create()->getInfoByWhere(true, $status, ['ord', 'ASC']);

            // 如果前端调用的是状态为0（未启用）的数据就不做树形整理
            // 没传 status参数 或 传 1 的默认给已启用的树形结构数据
            if ( empty($status) || $status['status'] == 1 )
                $list = Utils::handleTreeList($list);

        } catch (\Exception $e) {
            var_dump('Server\Menu-' . 'getMenuTree');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $list ?: $list;
    }

    /**
     * 通过菜单绑定权限id，获取该权限的权限树，转换成数组返回
     * @param $pid
     * @return array
     * @throws \Throwable
     */
    public static function getMenuPermissionList($pid)
    {
        if (!$pid) {
            return [];
        }
        $arr = Permissions::getParentIds($pid);
        $arr[] = $pid;
        return $arr;
    }

    /**
     * 添加 / 更新菜单
     * @param $params
     * @return array|bool
     * @throws \Throwable
     */
    public static function saveMenu($params)
    {
        //$data['permission_id'] = !empty(intval($params['permission_id'])) ? intval($params['permission_id']) : '';
        //$data['parent_id'] = !empty(intval($params['parent_id'])) ? intval($params['parent_id']) : '';
        //$data['permission_name'] = !empty($params['permission_name']) ? $params['permission_name'] : '';
        //$data['display_name'] = !empty($params['display_name']) ? $params['display_name'] : '';
        $data = [];
        if (isset($params['parent_id'])) $data['parent_id'] = intval($params['parent_id']);
        if (isset($params['status']))  $data['status'] = intval($params['status']);

        if (!empty($params['permission_id'])) $data['permission_id'] = intval($params['permission_id']);
        if (!empty($params['permission_name'])) $data['permission_name'] = $params['permission_name'];
        if (!empty($params['display_name'])) $data['display_name'] = $params['display_name'];

        if (!empty($params['icon']))  $data['icon'] = $params['icon'];
        if (!empty($params['url']))  $data['url'] = $params['url'];
        if (!empty($params['additional']))  $data['additional'] = $params['additional'];
        if (!empty($params['description']))  $data['description'] = $params['description'];

        if (!empty($params['id']))  $data['id'] = $params['id'];

        return self::save($data, true, 'Menu');
    }

}