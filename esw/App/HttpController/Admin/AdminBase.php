<?php


namespace App\HttpController\Admin;

use App\HttpController\Base;
use App\Lib\Auth\Aes;
use App\Lib\StatusCode;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Message\Status;
use App\Server\Admin\Permissions;

/* Admin 模块下的基类*/
class AdminBase extends Base
{

    public $user = null;


    /**
     * 根据需求重写Base类的拦截器，加入了 登录状态检测、权限拦截
     * @param string|null $action
     * @return bool|null
     * @throws \Throwable
     */
    public function onRequest(?string $action): ?bool
    {
        /* 请求参数处理 */
        $this->getParams();

        // 登录状态检测
        if (!$this->userCheck()) return false;

        // 权限拦截
        if (!$this->PermissionCheck()) return false;

        /* 加载请求参数自动验证器 */
        if(!$this->autoValidate($action)) return false;

        return true;
    }


    /**
     * 登录状态检测,并保存user信息
     * @param bool $type
     * @return bool
     */
    public function userCheck($type = true) {
        $user = \App\Server\Admin\User::check($type);
        if (empty($user)){
            $this->writeJson(Status::CODE_BAD_REQUEST,'登录失效，请重新登录');
            return false;
        }
        $this->user = $user;
        return true;
    }


    /**
     *  根据需求重写 getParams()
     */
    public function getParams() {
        //$params = $this->request()->getRequestParam();
        parent::getParams();

        // 后台分页相关参数通用处理
        $this->params['current_page'] = !empty($this->params['current_page']) ? intval($this->params['current_page']) : 1;
        $this->params['page_size'] = !empty($this->params['page_size']) ? intval($this->params['page_size']) : 5;
        $this->params['from'] = ($this->params['current_page'] - 1) * $this->params['page_size'];

        //$this->params = $params;
    }


    /**
     * 权限校验
     * @return bool
     * @throws \Throwable
     */
    public function PermissionCheck() {
        try{
            $roleId = $this->user['role_id'];
            $adminIni = \Yaconf::get('es_conf.admin');

            if ( $roleId != intval($adminIni['super_admin_id']) ){
                $url = $this->request()->getUri()->getPath();
                // 获取用户所拥有的所有的权限名
                $userPermissions = Permissions::getUserAllPermissions( $this->user['id'] );

                // 获取用户所访问的URL对应的权限名
                $uriPermissions = Permissions::getPermissionsFromUri();
                $uriPermissions = $uriPermissions[$url] ?? [];

                // 求交集
                if ( count(array_intersect($userPermissions,$uriPermissions)) == 0 ) {
                    $this->writeJson( StatusCode::ERR_NOT_ACCESS,'权限不够哦');
                    return false;
                }
            }
        }catch (\Exception $e){
            var_dump('Controller\AdminBase-'.'PermissionCheck');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),StatusCode::ERR_SERVER);
        }
        return true;
    }

    /**
     * 拦截对内置管理员相关的修改操作
     * @param $id
     * @return bool
     */
    public function adminUserLj($id) {
        $id = !empty($id) ? intval($id) : null;
        if ($id == \Yaconf::get('es_conf.admin.super_admin_id')) {
            throw new \Exception( '错误,系统内置用户不允许操作');
            //$this->writeJson(110, '错误,系统内置用户不允许操作');
            //$this->response()->end();
        }
    }


    /**
     * 根据需求变更 重写writeJson方法
     * @param int $statusCode
     * @param null $result
     * @param null $msg
     * @return bool
     */
    public function writeJson($statusCode = 200, $msg = null, $result = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                //'qid' => $this->request()->getHeader('qid'),
                "code" => $statusCode,
                "msg" => $msg,
                "data" => !empty($result) ? Aes::encrypt(json_encode($result)) : null
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }


}