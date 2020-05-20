<?php


namespace App\Lib;

/**
 * @Constants
 * 自定义业务代码规范如下：
 * 授权相关，1001……
 * 用户相关，2001……
 * 业务相关，3001……
 */
class StatusCode
{

    /**
     * @Message("ok")
     */
    const SUCCESS = 200;

    /**
     * @Message("常规警告！")
     */
    const WARNING = 110;

    /**
     * @Message("Internal Server Error!")
     */
    const ERR_SERVER = 500;

    /**
     * @Message("Internal ACCESS Error!")
     */
    const ERR_ACCESS = 400;


    /**
     * @Message("无权限访问！")
     */
    const ERR_NOT_ACCESS = 1001;

    /**
     * @Message("令牌过期！")
     */
    const ERR_EXPIRE_TOKEN = 1002;

    /**
     * @Message("令牌无效！")
     */
    const ERR_INVALID_TOKEN = 1003;

    /**
     * @Message("令牌不存在！")
     */
    const ERR_NOT_EXIST_TOKEN = 1004;



    /**
     * @Message("请登录！")
     */
    const ERR_NOT_LOGIN = 2001;

    /**
     * @Message("用户信息错误！")
     */
    const ERR_USER_INFO = 2002;

    /**
     * @Message("用户不存在！")
     */
    const ERR_USER_ABSENT = 2003;


    /**
     * @Message("业务逻辑异常！")
     */
    const ERR_EXCEPTION = 3001;

    /**
     * 用户相关逻辑异常
     * @Message("用户密码不正确！")
     */
    const ERR_EXCEPTION_USER = 3002;

    /**
     * 文件上传
     * @Message("文件上传异常！")
     */
    const ERR_EXCEPTION_UPLOAD = 3003;


}