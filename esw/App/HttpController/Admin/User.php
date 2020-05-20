<?php


namespace App\HttpController\Admin;

use App\Lib\Redis\RedisSession;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;
use App\Model\Admin\User as UserModel;
use App\Server\Admin\User as UserServer;
use App\Lib\StatusCode;


/**
 * 避免登录被拦截所以：不继承AdminBase 因为 AdminBase 专为其他后台页面做了登录状态拦截
 * Class User
 * @package App\HttpController\Admin
 */
class User extends AdminBase
{

    //const SUPER_USER_ID = 1;

    /**
     * 当前控制器的所有action 需要校验的 param 以及对应的规则
     * @param string|null $action
     * @return Validate|null
     */
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'list':
                {
                    $v->addColumn('current_page', 'current_page')->required('不能为空')->integer('必须是数字');
                    $v->addColumn('page_size', 'page_size')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'get_info':
                {
                    $v->addColumn('id', '用户id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'delete':
                {
                    $v->addColumn('id', '用户id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'store':
                {
                    $v->addColumn('id', '用户id')->optional()->integer('必须是数字');
                    $v->addColumn('mobile', '手机号')->required('不能为空')->integer('必须是数字')->length(11, '必须是11位');
                    //$v->addColumn('password', '密码')->required('不能为空');
                    $v->addColumn('username', '用户名')->required('不能为空');
                    $v->addColumn('role_id', '角色id')->required('不能为空')->integer('必须是数字');
                    break;
                }
        }
        return $v;
    }

    /**
     * 用户管理列表
     */
    public function list()
    {
        $data = UserServer::getList($this->params);
        if (empty($data)) {
            return $this->writeJson(StatusCode::WARNING, '稍后再试');
        }
        return $this->writeJson(Status::CODE_OK, 'ok', $data);
    }

    /**
     * （编辑View）获取用户信息
     * @return bool
     * @throws \Throwable
     */
    public function get_info()
    {
        try {
            $info = UserModel::create()->getById($this->params['id'], [], ['password', 'session_id']);
        } catch (\Exception $e) {
            var_dump('Controller\User-' . 'get_info');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($info)) {
            return $this->writeJson(StatusCode::WARNING, '请稍后再试');
        }
        return $this->writeJson(Status::CODE_OK, 'ok', $info);

    }

    /**
     * （编辑View）获取角色数据
     * @return bool
     * @throws \Exception
     */
    public function get_roles()
    {
        $list = \App\Server\Admin\Roles::getList();
        return $this->writeJson(Status::CODE_OK, 'ok', $list);
    }

    /**
     * 新增 / 修改
     * @return bool
     * @throws \Exception
     */
    public function store()
    {
        $this->adminUserLj(!empty($this->params['id']) ? $this->params['id'] : '');

        $id = UserServer::saveUser($this->params);
        if (empty($id))
            return $this->writeJson(StatusCode::WARNING, '请稍后再试');
        return $this->writeJson(Status::CODE_OK, 'ok', $id);

    }

    public function delete()
    {
        $id = intval($this->params['id']);
        $this->adminUserLj($id);
        try {
            if ($id == UserServer::check()) {
                return $this->writeJson(StatusCode::WARNING, '错误,不允许删自己');
            }
            $res = UserModel::create()->delete($id);
        } catch (\Exception $e) {
            var_dump('Controller\User-' . 'delete');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($res))
            return $this->writeJson(StatusCode::WARNING, '请稍后再试');
        return $this->writeJson(Status::CODE_OK, 'ok');
    }

}