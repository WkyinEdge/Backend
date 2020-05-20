<?php


namespace App\HttpController\Admin;

use App\Model\Admin\Roles as RolesModel;
use App\Server\Admin\Roles as RolesServer;
use App\Server\Admin\RolesWithPermissions;
use EasySwoole\Http\Message\Status;
use App\Lib\StatusCode;
use EasySwoole\Validate\Validate;

class Roles extends AdminBase
{
    /**
     * 当前控制器的所有action 需要校验的 param 以及对应的规则
     * @param string|null $action
     * @return Validate|null
     */
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action){
            case 'get_info':{
                $v->addColumn('id','角色id')->required('不能为空')->integer('必须是数字');
                break;
            }
            case 'store':{
                $v->addColumn('display_name','角色名')->required('不能为空')
                    ->lengthMax(20,'长度不能超过20')
                    ->lengthMin(2,'长度不能小于2');
                $v->addColumn('name','角色标识')->required('不能为空')
                    ->lengthMax(20,'长度不能超过20')
                    ->lengthMin(2,'长度不能小于2');
                //$v->addColumn('description','说明');
                break;
            }
            case 'get_permissions':{
                $v->addColumn('id','角色id')->required('不能为空')->integer('必须是数字');
                break;
            }
            case 'save_permissions':{
                $v->addColumn('role_id','角色id')->required('不能为空')->integer('必须是数字');
                $v->addColumn('permissions_id','权限ids')->required('不能为空');
                break;
            }
            case 'delete':{
                $v->addColumn('id','角色id')->required('不能为空')->integer('必须是数字');
                break;
            }
            case 'get_users':{
                $v->addColumn('role_id','角色id')->required('不能为空')->integer('必须是数字');
                break;
            }
            case 'search_user':{
                $v->addColumn('role_id','角色id')->required('不能为空')->integer('必须是数字');
                //$v->addColumn('search','搜索内容')->required('不能为空')->integer('必须是数字');
                break;
            }
            case 'save_user':{
                $v->addColumn('user_id','用户id')->required('不能为空')->integer('必须是数字');
                $v->addColumn('role_id','角色id')->required('不能为空')->integer('必须是数字');
                break;
            }
            case 'remove_user':{
                $v->addColumn('user_id','用户id')->required('不能为空')->integer('必须是数字');
                break;
            }
        }
        return $v;
    }

    /**
     *  用户 menu 路由获取
     */
    public function list() {

        $res = RolesModel::create()->getPage($this->params['current_page'], $this->params['page_size']);
        $data = [
            'pages' => $res['pageInfo'],
            'list' => $res['data']
        ];
        return $this->writeJson(Status::CODE_OK,'OK',$data);
    }

    /**
     * （权限绑定列表）获取指定角色权限信息
     * @return bool
     * @throws \Throwable
     */
    public function get_permissions()
    {
        $id = $this->params['id'];
        $data = RolesServer::getRolePermissions($id);
        return $this->writeJson(Status::CODE_OK,'OK',$data);
    }

    /**
     * 保存 角色权限绑定
     * @return bool
     * @throws \Exception
     */
    public function save_permissions()
    {
        $permissions_id = json_decode($this->params['permissions_id']);
        if (empty($permissions_id)) {
            return $this->writeJson(StatusCode::WARNING,'参数不合法');
        }
        $this->params['permissions_id']=$permissions_id;

        //RolesWithPermissions::create()->saveRolesPermissions($this->params);
        RolesWithPermissions::saveRolesPermissions($this->params);

        return $this->writeJson(Status::CODE_OK,'ok');
    }

    /**
     * 获取指定角色信息
     * @return bool
     * @throws \Throwable
     */
    public function get_info(){
        $id = $this->params['id'];
        try{
            $info = RolesModel::create()->getById($id);
        }catch (\Exception $e){
            var_dump('Controller\Roles-'.'get_info');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($info))
            return $this->writeJson(StatusCode::WARNING,'数据错误，请稍后重试');
        return $this->writeJson(Status::CODE_OK,'OK', $info);
    }

    /**
     * 添加 / 修改角色
     * @return bool
     * @throws \Throwable
     */
    public function store() {
        $id = RolesServer::saveRoles($this->params);
        if (empty($id))
            return $this->writeJson(StatusCode::WARNING,'请稍后再试');
        return $this->writeJson(Status::CODE_OK,'ok',$id);
    }

    /**
     * delete
     * @return bool
     * @throws \Throwable
     */
    public function delete()
    {
        try{
            $id = $this->params['id'];
            $res = RolesModel::create()->delete($id);
        }catch (\Exception $e){
            var_dump('Controller\Role-'.'delete');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($res))
            return $this->writeJson(StatusCode::WARNING,'请稍后再试');
        return $this->writeJson(Status::CODE_OK,'ok');
    }

    /**
     * 获取角色内置User列表
     * @return bool
     * @throws \Throwable
     */
    public function get_users()
    {
        try{
            $roleId = $this->params['role_id'];
            $res = \App\Model\Admin\User::create()->getPage($this->params['current_page'], $this->params['page_size'], ['role_id' => $roleId]);
        }catch (\Exception $e){
            var_dump('Controller\Role-'.'get_users');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($res))
            return $this->writeJson(StatusCode::WARNING,'稍后再试');
        $data = [
            'pages' => $res['pageInfo'],
            'list' => $res['data'],
        ];
        return $this->writeJson(Status::CODE_OK,'ok',$data);
    }

    /**
     * （添加成员）搜索用户
     * @return bool
     * @throws \Throwable
     */
    public function search_user()
    {
        if (empty($this->params['search']))
            return;
        $list = \App\Server\Admin\User::searchUserList($this->params['role_id'],$this->params['search']);
        //var_dump($list);
        return $this->writeJson(Status::CODE_OK,'ok', $list);
    }

    /**
     * 保存成员内的用户
     * @return bool
     * @throws \Throwable
     */
    public function save_user()
    {
        $this->adminUserLj($this->params['user_id']);
        try{
            $res = \App\Model\Admin\User::create()->updateById($this->params['user_id'],['role_id' => $this->params['role_id']]);

        }catch (\Exception $e){
            var_dump('Controller\Role-'.'save_user');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (!$res){
            return $this->writeJson(StatusCode::WARNING,'稍后再试');
        }
        return $this->writeJson(Status::CODE_OK,'ok');
    }

    /**
     * 移除成员内的用户
     * @return bool
     * @throws \Throwable
     */
    public function remove_user()
    {
        $this->adminUserLj($this->params['user_id']);
        try{
            $res = \App\Model\Admin\User::create()->updateById($this->params['user_id'],['role_id' => 0]);
        }catch (\Exception $e){
            var_dump('Controller\Role-'.'remove_user');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (!$res){
            return $this->writeJson(StatusCode::WARNING,'稍后再试');
        }
        return $this->writeJson(Status::CODE_OK,'ok');
    }



}