<?php

namespace App\Lib\Auth;

use App\Lib\StatusCode;
use EasySwoole\FastCache\Cache;

//use App\Lib\Auth\Aes;

/**
 * Iauth相关
 * Class IAuth
 */
class IAuth {

    /**
     * 设置密码
     * @param string $data
     * @return string
     */
    public static  function setPassword($data) {
        return md5($data . \Yaconf::get('es_conf.auth.password_pre_halt'));
    }

    /**
     * 生成每次请求的sign （测试场景）
     * @param array $data
     * @return string
     */
    public static function setSign($data = [])
    {
        //ksort($data);//$string = http_build_query($data);

        $string = json_encode($data);
        // 通过aes来加密
        $string = Aes::encrypt($string);

        return $string;
    }

    /**
     * 检查sign是否正常
     * @param $data
     * @return array
     */
    public static function checkSignPass($data)
    {
        if (empty($data['sign'][0]))
            return self::result('（sign：null）请求无效！', StatusCode::ERR_ACCESS);

        //if (empty($data['tid'][0]))
        //    return self::result('（tid：null）请求无效！', StatusCode::ERR_ACCESS);


        $sign = [
            'sign' => $data['sign'][0],
            'tid' => $data['tid'][0]
        ];
        $str = Aes::decrypt($sign['sign']);

        if(empty($str))
            return self::result('（签名）请求无效：sign错误！');

        $arr = json_decode($str, true);

        // 验证签名前后的 tokenId 是否一致（防篡改）
        if(!is_array($arr) || /*empty($arr['tid']) ||*/ $arr['tid'] != $sign['tid'] || empty($arr['time']))
            return self::result('（签名）请求无效：参数错误！');

        //if( !\Yaconf::get('es_conf.auth.app_debug') )
        //{   // 如果是生产环境
            // 做时效检验
            if ((time() - ceil($arr['time'] / 1000)) > \Yaconf::get('es_conf.auth.app_sign_time'))
                return self::result('（签名）请求无效：超时异常！');//return false;

            // 防重放检验
            if (Cache::getInstance()->get($sign['sign']))
                return self::result('（签名）请求无效：重复的请求！'); //return false;

            // 全部通过后存入缓存 标识防重放
            Cache::getInstance()->set($sign['sign'], 1, \Yaconf::get('es_conf.auth.app_sign_cache_time'));
        //}
        return self::result('（sign）授权通过！', StatusCode::SUCCESS);
    }

    /**
     * 返回结果集
     * @param $msg
     * @param int $code
     * @return array
     */
    public static function result($msg, $code = StatusCode::ERR_NOT_ACCESS)
    {
        return [
            'code' => $code,
            'msg' => $msg
        ];
    }

    /**
     * 设置登录的token  - 唯一性的
     * @param string $account
     * @return string
     */
    public static function setAppLoginToken($account = '') {
        $str = md5(uniqid(md5(microtime(true)), true));
        $str = sha1($str.$account);
        return $str;
    }

}