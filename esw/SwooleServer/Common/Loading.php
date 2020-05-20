<?php

//namespace Common;

//class Loading
//{

/**
 * 类库自动加载方法
 * @param $className
 */
function autoLoad($className)
{
    /**
     * 获取项目根路径（也就是上一级路径）
     * __DIR__ 表示当前绝对路径（不包含文件名），
     * __FILE__ 表示包含文件名的绝对路径，
     * dirname(dirname(__FILE__)) 表示上一级路径
     */
    $rootDir = dirname(dirname(dirname(__FILE__)));
    // 把 \ 替换成 /  为了兼容Linux文件路径
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $rootDir . '\\' . $className) . '.php';

    if (is_file($fileName)) {//判断文件是否存在
        require_once($fileName);
    } else {
        var_dump($fileName . 'is not exist');
        //echo $fileName . 'is not exist';
    }
}

//}