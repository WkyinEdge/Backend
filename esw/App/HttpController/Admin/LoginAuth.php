<?php


namespace App\HttpController\Admin;

use App\HttpController\Base;
use App\Lib\Auth\Aes;
use App\Lib\Redis\RedisSession;
use App\Lib\Utils;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;
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
class LoginAuth extends Base
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
            case 'login':
                {
                    $v->addColumn('account', '用户')->required('不能为空')
                        ->lengthMax(20, '长度不能超过20')
                        ->lengthMin(2, '长度不能小于2');
                    $v->addColumn('password', '密码')->required('不能为空')
                        ->lengthMax(20, '长度不能超过20')
                        ->lengthMin(3, '长度不能小于3');;
                    break;
                }
        }
        return $v;
    }

    /**
     * 后台登录
     * @return bool
     * @throws \Throwable
     */
    public function login()
    {
        $user = UserServer::handleLogin($this->params);
        // $ip = $_POST['ip'];
        $save = [
            'last_login_ip' => $_POST['ip'],
            'last_login_time' => time()
        ];

        $token = $this->makeToken($user, $save);

        // Task异步更新MySql不能调用封装的updataById()，因为Task是独立进程 不属于当前onRequest会话进程，
        //无法获取当前会话的sid，导致执行到model中的check()时读取session 报错，
        TaskManager::getInstance()->async(function () use ($user, $save) {
            UserModel::create()->get($user['id'])->update($save);
        });

        $resInfo = [
            'id' => $user['id'] ?? '',
            'mobile' => $user['mobile'] ?? '',
            'username' => $user['username'] ?? '',
            'nickname' => $user['nickname'] ?? '',
            'avatar' => $user['avatar'] ?? '',
        ];
        return $this->writeJson(Status::CODE_OK, 'ok', $resInfo +
            ($token ? [
                'token' => $token
            ] : []));
    }

    /**
     * token 处理
     * @param $user
     * @param $save
     * @return string
     */
    private function makeToken( $user, $save )
    {
        $clientType = $this->request()->getHeader('client-type');
        $token = '';

        $redisSession = RedisSession::getInstance();
        $sid = $redisSession->SessionId();

        if ( !empty($clientType[0]) && $clientType[0] === 'admin' ) {
            /**（cookie方式验证登录权限）刷新 session 和 cookie **/
            $this->response()->setCookie('esw_session', $sid);
        }
        else {
            /** （token方式验证登录）生成token 并response给客户端 **/
            $token = $sid;
        }

        /***************************************  踢下线功能 *******************************/
        // 检测当前用户是否已登录过，做下线处理
        $oldToken = $redisSession->isExist( $user['id'] );
        if ( !empty($oldToken) )
            $redisSession->destory( $oldToken );
        // 加入 userId => token 的关系绑定
        $redisSession->bind( $user['id'], $sid);
        /***************************************** end ***********************************/

        // 保存 session
        $redisSession->write(\Yaconf::get('es_conf.admin.LOGIN_TAG'), array_merge($user, $save));

        return $token;
    }

    /**
     * 退出登录
     * @return bool
     * @throws \Throwable
     */
    public function logout()
    {
        $res = RedisSession::getInstance()->destory();
        if ($res) {
            return $this->writeJson(Status::CODE_OK, 'OK');
        }
        return $this->writeJson(Status::CODE_BAD_REQUEST, '操作失败');

    }



    /**
     * User类单独重写writeJson方法
     * @param int $statusCode
     * @param null $result
     * @param null $message
     * @return bool
     */
    public function writeJson($statusCode = 200, $message = null, $result = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                //'qid' => $this->request()->getHeader('qid'),
                "code" => $statusCode,
                "msg" => $message,
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