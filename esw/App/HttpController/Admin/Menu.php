<?php


namespace App\HttpController\Admin;

use App\Lib\StatusCode;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;
use App\Server\Admin\Menu as MenuServer;
use App\Model\Admin\Menu as MenuModel;

class Menu extends AdminBase
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
                    //$v->addColumn('permission_name', '权限名')->required('不能为空');
                    $v->addColumn('permission_id', '权限id')->required('不能为空')->integer('必须是数字');
                    $v->addColumn('display_name', '菜单名')->required('不能为空');
                    $v->addColumn('parent_id', 'parent_id')->required('不能为空')->integer('必须是数字');;
                    break;
                }
            case 'get_info':
                {
                    $v->addColumn('id', '菜单id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'delete':
                {
                    $v->addColumn('id', '菜单id')->required('不能为空')->integer('必须是数字');
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
     *  获取用户对应权限的 menu 表
     */
    public function user_menu()
    {
        $list = MenuServer::getUserMenuList($this->user['id']);
        return $this->writeJson(Status::CODE_OK, 'OK', $list);
    }

    /**
     * 菜单管理列表
     */
    public function list()
    {
        $status = [];
        if ( isset($this->params['status']) )
            $status = ['status' => $this->params['status']];
        $list = MenuServer::getMenuTree($status);

        return $this->writeJson(Status::CODE_OK, 'OK', $list);
    }

    /**
     * 获取子菜单列表（View：添加 / 编辑子菜单）
     */
    public function permissions_list()
    {
        $list = \App\Server\Admin\Permissions::getPermissionsTreeList();

        return $this->writeJson(Status::CODE_OK, 'OK', $list);
    }

    /**
     * 添加 / 修改 Api 区别在于是否有主键
     */
    public function store()
    {
        $params = $this->params;

        // 不是一级菜单
        if (intval($params['parent_id']) !== 0) {
            //  验证 permission_name 不能为空，必须传控制好源头
            if(empty($params['permission_name']))
                return $this->writeJson(StatusCode::WARNING, 'permission_name 不能为空');

            // $parentRow = MenuModel::create()->getById($params['parent_id']);// if ($parentRow['parent_id'])
            // 添加操作拦截
            if ( empty($params['id']) )
                return $this->writeJson(StatusCode::WARNING, '不支持添加三级菜单');
        }
        $id = MenuServer::saveMenu($params);
        if (empty($id)) {
            return $this->writeJson(StatusCode::WARNING, '保存失败未知错误');
        }
        return $this->writeJson(Status::CODE_OK, 'ok', $id);
    }

    /**
     * 获取当前菜单信息
     */
    public function get_info()
    {
        $id = intval($this->params['id']);
        try {
            $info = MenuModel::create()->getById($id);

            $pid = $info['permission_id'] ?? 0;
            $menuPermissions = MenuServer::getMenuPermissionList($pid);

        } catch (\Exception $e) {
            var_dump('Controller\Menu-' . 'get_info');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        $data = [
            'info' => $info,
            'menu_permissions' => $menuPermissions
        ];
        return $this->writeJson(Status::CODE_OK, 'OK', $data);
    }

    /**
     * 获取一级菜单
     * @return bool
     * @throws \Throwable
     */
    public function getParentMenu()
    {
        $list = MenuModel::create()->field(['id','display_name'])->getInfoByWhere(true, ['parent_id' => 0], ['ord', 'ASC']);

        return $this->writeJson(Status::CODE_OK, 'OK', $list);
    }

    /**
     * 删除菜单
     */
    public function delete()
    {
        $id = intval($this->params['id']);
        $info = MenuServer::deleteInfo($id, 'Menu');
        //$info = !empty($info) ? 'ok' : '操作失败';
        return $this->writeJson(Status::CODE_OK, 'OK', $info);
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
        if (!is_array($ids)) {
            return $this->writeJson(StatusCode::WARNING, '请求参数错误');
        }
        $res = MenuServer::dragSort($ids, 'Menu');
        if (empty($res)) {
            return $this->writeJson(StatusCode::WARNING, '操作失败');
        }
        return $this->writeJson(Status::CODE_OK, 'ok');
    }


}