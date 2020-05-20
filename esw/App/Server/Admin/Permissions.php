<?php


namespace App\Server\Admin;

use App\Lib\Common\Common;
use App\Lib\Utils;
use App\Model\Admin\User;
use App\Model\Admin\Permissions as PermissionsModel;
use App\Server\Base;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Message\Status;


class Permissions extends Base
{
    /**
     * 超级管理员用户组id
     */
    const ROOT_ROLE_ID = 1;

    /**
     * 获取用户所拥有的所有权限（只获取权限名）
     * @param $userId
     * @return array|null
     * @throws \Throwable
     */
    public static function getUserAllPermissions($userId)
    {
        try {
            // 调用权限
            $role = User::create()->get($userId)->roles();
            if (empty($role)) {
                var_dump('Server\Permissions-' . 'getUserAllPermissions');
                throw new \Exception(__METHOD__ . ' -- ' . '无权访问，请联系管理员');
            }
            $roleId = $role->id;

            if ($roleId == self::ROOT_ROLE_ID) {
                //超级管理员拥有所有权限
                $lists = PermissionsModel::create()->column('name');
            } else {
                //不是超级管理员，就获取本身对应的权限
                $arr = User::create()->get($userId)->roles()->rolesWithPermissions();//->permissions();
                $lists = [];
                foreach ($arr as $list) {
                    $lists[] = $list->permissions()->name;
                }
            }
        } catch (\Exception $e) {
            var_dump('Server\Permissions-' . 'getUserAllPermissions');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $lists ?: [];
    }

    /**
     * 获取权限表（完整）
     * @return array
     * @throws \Throwable
     */
    public static function getList($status = [])
    {
        try {
            $res = PermissionsModel::create()->getInfoByWhere(true, $status, ['ord' , 'ASC']);

        } catch (\Exception $e) {
            var_dump('Server\Permissions-' . 'getList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $res ?: [];
    }

    /**
     * 获取树形结构的权限列表
     * @return mixed
     * @throws \Throwable
     */
    public static function getPermissionsTreeList($status = [])
    {
        $list = self::getList($status);
        $list = json_decode(json_encode($list), true);
        foreach ($list as &$v) {
            $v['effect_uri_alias'] = $v['effect_uri'] && mb_strlen($v['effect_uri']) > 24 ? mb_substr($v['effect_uri'], 0, 24) . '...' : '';
        }
        unset($v);
        // 如果前端调用的是状态为0（未启用）的数据就不做树形整理
        // 没传 status参数 或 传 1 的默认给已启用的树形结构数据
        if ( empty($status) || $status['status'] == 1 )
            $list = Utils::handleTreeList($list);

        return $list;
    }

    /**
     * 获取父级们的id，组成数组
     * @param $id
     * @return array
     * @throws \Throwable
     */
    public static function getParentIds($id)
    {
        try {
            $arr = [];
            $row = PermissionsModel::create()->getById($id);
            if ($row && $row['parent_id']) {
                $arr[] = $row['parent_id'];
                $arr = array_merge(self::getParentIds($row['parent_id']), $arr);
            }
        } catch (\Exception $e) {
            var_dump('Server\Permissions-' . 'getParentIds');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $arr;
    }

    /**
     * 将角色权限列表，按着权限父级分组
     * @param array $list
     * @return array
     */
    public static function handelPermissionsGroup($list = [])
    {
        if (!$list) {
            return [];
        }
        $tmp = [];
        foreach ($list as $v) {
            $tmp[$v['parent_id']][] = $v['id'];
        }
        return $tmp;
    }

    /**
     * 添加 / 更新权限信息
     * @param $params
     * @return array|bool
     * @throws \Throwable
     */
    public static function savePermissions($params)
    {
        $data = [];
        if (isset($params['parent_id']))  $data['parent_id'] = intval($params['parent_id']);
        if (isset($params['status']))  $data['status'] = intval($params['status']);

        if (!empty($params['display_name']))  $data['display_name'] = $params['display_name'];

        if (!empty($params['effect_uri']))  $data['effect_uri'] = $params['effect_uri'];

        if (!empty($params['description']))  $data['description'] = $params['description'];

        if (!empty($params['id']))  $data['id'] = $params['id'];

        // 构造查重条件
        $where = '';
        if (!empty($params['name']) && $params['name'] != 'null') {
            $data['name'] = $params['name'];
            $where .= "name='" . $data['name'] . "'";
        }

        return self::save($data, true, 'Permissions', $where);

    }

    /**
     * 获取 [uri => Pname,....] 对应的权限标识 用来做权限拦截
     * @return array
     * @throws \Throwable
     */
    public static function getPermissionsFromUri()
    {
        //$c = Cache::getInstance()->get('permissions-from-uri');
        /*if ($c) {
            return $c;
        }*/
        $res = [];
        $list = self::getList();
        foreach ($list as $v) {
            $tmp = $v['effect_uri'];
            if (!$tmp) {
                continue;
            }
            $tmp = explode(',', $tmp);
            foreach ($tmp as $v1) {
                if (trim($v1)) {
                    $res[trim($v1)][] = $v['name'];
                }
            }
        }
       // var_dump($res);
        //Cache::getInstance()->set('permissions-from-uri', $res);
        return $res;
    }


}