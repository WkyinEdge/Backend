<?php


namespace App\HttpController\Admin;

use EasySwoole\Http\Message\Status;
use App\Lib\StatusCode;
use EasySwoole\Validate\Validate;
use App\Server\Admin\Permissions as PermissionsServer;
use App\Model\Admin\Permissions as PermissionsModel;


class Permissions extends AdminBase
{
    /**
     * 当前控制器的所有action 需要校验的 param 以及对应的规则
     * @param string|null $action
     * @return Validate|null
     */
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'store':
                {
                    $v->addColumn('display_name', '权限名')->required('不能为空');
                    $v->addColumn('name', '权限标识')->required('不能为空');
                    $v->addColumn('parent_id', 'parent_id')->required('不能为空')->integer('必须是数字');;
                    break;
                }
            case 'get_info':
                {
                    $v->addColumn('id', '权限id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'delete':
                {
                    $v->addColumn('id', '权限id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'order':
                {
                    $v->addColumn('ids', 'ids')->required('不能为空');
                    break;
                }
        }
        return $v;
    }

    /**
     * 获取用户对应角色对应权限（用来生成权限菜单树）
     * @return bool
     * @throws \Throwable
     */
    public function user_permissions()
    {
        $list = PermissionsServer::getUserAllPermissions($this->user['id']);

        return $this->writeJson(Status::CODE_OK, 'ok', $list);
    }

    /**
     * 权限管理列表
     * @return bool
     * @throws \Throwable
     */
    public function list()
    {
        $status = [];
        if ( isset($this->params['status']) )
            $status = ['status' => $this->params['status']];
        $list = PermissionsServer::getPermissionsTreeList($status);

        return $this->writeJson(Status::CODE_OK, 'ok', $list);
    }

    /**
     * 添加 / 修改权限信息 区别在于是否有主键
     */
    public function store()
    {
        // 验证 permission_name 不能为空，必须传控制好源头
        $params = $this->params;

        if (intval($params['parent_id']) != 0) {
            $parentRow = PermissionsModel::create()->getById($params['parent_id']);
            if ($parentRow['parent_id'])
                return $this->writeJson(StatusCode::WARNING, '不支持三级菜单');
        }
        $id = PermissionsServer::savePermissions($params);
        if (empty($id)) {
            return $this->writeJson(StatusCode::WARNING, '失败,未知错误');
        }
        return $this->writeJson(Status::CODE_OK, 'ok', $id);
    }

    /**
     * 获取当前权限信息
     */
    public function get_info()
    {
        $id = intval($this->params['id']);
        try {
            $info = PermissionsModel::create()->getById($id);
        } catch (\Exception $e) {
            var_dump('Controller\Permissions-' . 'get_info');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, 'ok', $info);
    }

    /**
     * 删除权限
     */
    public function delete()
    {
        $id = intval($this->params['id']);
        $info = PermissionsServer::deleteInfo($id, 'Permissions');
        if (empty($info)) {
            return $this->writeJson(StatusCode::WARNING, '操作失败');
        }
        return $this->writeJson(Status::CODE_OK, 'ok',$info);
    }

    /**
     * 拖拽排序
     * @return bool
     * @throws \Throwable
     */
    public function order()
    {
        $ids = $this->params['ids'];
        if ( is_string($ids) ) {
            $ids = explode(',', $this->params['ids']);
        }
        if (empty($ids)) {
            return $this->writeJson(StatusCode::WARNING, '请求参数错误');
        }
        $res = PermissionsServer::dragSort($ids, 'Permissions');
        if (empty($res)) {
            return $this->writeJson(StatusCode::WARNING, '操作失败');
        }
        return $this->writeJson(Status::CODE_OK, 'ok', $res);
    }

}