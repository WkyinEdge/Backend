<?php

/**
 * 定义规则： 规定客户端访问的URL路径 => [ 控制器，处理方法 ]
 */
return
    [   // 支持分组，可根据控制器分组
        [
            '/' => ['Controller' => 'Index', 'action' => 'index'],
            '/reload' => ['Controller' => 'Index', 'action' => 'reload'],
        ],
        // Index 控制器
        [
            '/index/index' => ['Controller' => 'Index', 'action' => 'index'],
            '/index/test' => ['Controller' => 'Index', 'action' => 'test'],
        ],

    ];