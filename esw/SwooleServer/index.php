<?php
//declare(strict_types=1);
/**
 * 自己实现一个面向对象 swoole 服务器
 */


/** （用来加载自己写的类） 程序入口处注册类库自动加载，然后全程享受，不用每个控制器都去注册 */
//require_once "./Common/Loading.php";
//spl_autoload_register("autoLoad");

/** （用来加载第三方的类库）引入 composer 自动加载 */
require_once "../vendor/autoload.php";


/**
 * 协程环境有两种自动开启的方式，异步风格 Server 或者 Process 和 Process\Pool 的 start()方法，其他手搓的方式都要用Co\run() 手动创建
 * 只要在设置项 set() 中配置 enable_coroutine 就行
 * （异步风格）默认自动开启协程环境并且遇到IO自动创建协程！不需要 Co\run() 手动开启环境，也不需要 go（）或者Co::create() 手动创建协程了
 */
// 一键协程化所有IO客户端 包括CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);


new SwooleServer\Http\HttpServer();