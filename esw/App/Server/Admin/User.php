<?php


namespace App\Server\Admin;

use App\Lib\Auth\IAuth;
use App\Lib\Redis\RedisSession;
use App\Lib\Utils;
use App\Server\Base;
use EasySwoole\Session\Session;
use App\Model\Admin\User as UserModel;

class User extends Base
{
    // Session 登录身份验证标识 key
    const LOGIN_TAG = 'LOGIN_AUTH';

    /**
     * 登录处理
     * @param array $params
     * @return array
     * @throws \Throwable
     */
    public static function handleLogin(array $params)
    {
        $key = Utils::isMobileNum($params['account']) ? 'mobile' : 'username';
        $where = [
            $key => $params['account']
        ];
        try {
            $user = UserModel::create()->getInfoByWhere(false, $where);
        }
        catch (\Exception $e) {
            var_dump('Server\User-' . 'handleLogin');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($user)){
            throw new \Exception('用户不存在');
        }
        if (IAuth::setPassword($params['password']) !== $user['password']) {
            throw new \Exception('密码错误');
        }
        return $user;
    }

    /**
     * 处理退出登录 销毁session
     * @param string $type
     * @return bool
     * @throws \Throwable
     */
    public static function logout($type = 'destroy')
    {
        $session = Session::getInstance();
        try {
            if ($type === 'destroy') $session->destroy();
            //if ($type === 'remove') $session->del(self::LOGIN_TAG);
        } catch (\Exception $e) {
            var_dump('Server\User-' . 'logout');
            throw new \Exception('会话异常');
        }
        return true;
    }

    /**
     * 检测用户登录状态，登录返回用户信息
     * 根据返回类型，判断是否返回用户信息，还是返回用户id
     * @param bool $type 是否返回当前用户数据
     * @return bool
     */
    public static function check( $type = false)
    {
        //$loginInfo = Session::getInstance()->get(self::LOGIN_TAG);
        $loginInfo = RedisSession::getInstance()->read(\Yaconf::get('es_conf.admin.LOGIN_TAG'));

        // 可以再加入客户端当前ip和 session中 user-last_login_ip对比判定，防内又防外
        //var_dump($_POST['ip']);
        if ( $_POST['ip'] != $loginInfo['last_login_ip'] ) return false;

        if (empty($loginInfo)) {
            return false;
        }
        if ($type === true) {
            return $loginInfo;
        }
        $uid = $loginInfo['id'];
        return $uid;
    }

    /**
     * 设置session
     */
    /*public static function setSession($user)
    {
        try {
            Session::getInstance()->set(self::LOGIN_TAG, $user);
        } catch (\Exception $e) {
            var_dump('Server\User-' . 'setSession');
            throw new \Exception('会话异常');
        }

    }*/

    /**
     * 获取用户列表 或 筛选列表
     * @param $params
     * @return array
     * @throws \Throwable
     */
    public static function getList($params)
    {
        try {
            $current_page = $params['current_page'];
            $page_size = $params['page_size'];
            $order = ['create_time', 'DESC'];
            $where = [];
            if (!empty($params['username'])) {
                $where['username'] = !empty($params['username']) ? trim($params['username']) : '';
            }
            if (!empty($params['mobile'])) {
                $where['mobile'] = !empty($params['mobile']) ? trim($params['mobile']) : '';
            }
            $res = UserModel::create()->getPage($current_page, $page_size, $where, $order, ['id', 'mobile', 'username', 'role_id', 'nickname', 'email', 'avatar', 'job_number', 'create_time']);
        } catch (\Exception $e) {
            var_dump('Server\User-' . 'getList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        $data = [];
        if (!empty($res)) {
            $data = [
                'pages' => $res['pageInfo'],
                'list' => $res['data']
            ];
        }
        return $data;
    }

    /**
     * 添加 / 修改用户
     * @param $params
     * @return bool|int|string
     * @throws \Exception
     */
    public static function saveUser($params)
    {
        if (!empty($params['id']) && $params['id'] != 'null') {
            $saveData['id'] = $params['id'];
        }
        if (!empty($params['password']) && $params['password'] != 'null') {
            $saveData['password'] = IAuth::setPassword(trim($params['password']));
        }
        if (!empty($params['nickname']) && $params['nickname'] != 'null') {
            $saveData['nickname'] = $params['nickname'];
        }
        if (!empty($params['avatar']) && $params['avatar'] != 'null') {
            $saveData['avatar'] = $params['avatar'];
        }
        $saveData['role_id'] = $params['role_id'];

        // 构造查重条件 多个查重条件 加()
        //$where = '(';
        $where = '';
        if (!empty($params['job_number']) && $params['job_number'] != 'null') {
            $saveData['job_number'] = $params['job_number'];
            $where .= "(job_number='" . $saveData['job_number'] . "'";
        }
        if (!empty($params['mobile']) && $params['mobile'] != 'null') {
            $saveData['mobile'] = $params['mobile'];
            $where .= $where ? ' or ' : '(';
            $where .= "mobile='" . $saveData['mobile'] . "'";
        }
        if (!empty($params['username']) && $params['username'] != 'null') {
            $saveData['username'] = $params['username'];
            $where .= $where ? ' or ' : '(';
            $where .= "username='" . $saveData['username'] . "'";
        }
        if (!empty($params['email']) && $params['email'] != 'null') {
            $saveData['email'] = $params['email'];
            $where .= $where ? ' or ' : '(';
            $where .= "email='" . $saveData['email'] . "')";
        }else
            $where .= ')';

        return self::save($saveData, false, 'User', $where);
    }

    /**
     * 根据手机号模糊搜索
     * @param $role_id
     * @param $search
     * @return array
     * @throws \Throwable
     */
    public static function searchUserList($role_id, $search)
    {
        if (empty($search)) {
            return [];
        }
        try {
            $where = [
                'role_id' => [$role_id, '!='],
                'mobile' => [$search . '%', 'like']
            ];
            $list = \App\Model\Admin\User::create()->getInfoByWhere(true, $where);

            foreach ($list as $k => $v) {
                $list[$k]['value'] = $v['nickname'] ? $v['mobile'] . '(' . $v['nickname'] . ')' : $v['mobile'];
            }
        } catch (\Exception $e) {
            var_dump('Server\User-' . 'searchUserList');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $list;
    }


}