<?php


namespace App\Lib;

/**
 * 做一些和反射机制有关的处理
 * Class ClassArr
 * @package App\Lib
 */
class ClassArr
{
    /**
     * 上传相关
     * @return array
     */
    public static function uploadClassStat()
    {
        return [
            'image' => '\App\Lib\Upload\ImageUpload',
            'video' => '\App\Lib\Upload\VideoUpload',
            'media' => '\App\Lib\Upload\MediaUpload',
        ];
    }

    public static function adminClassStat()
    {
        return [
            'Menu' => '\App\Model\Admin\Menu',
            'Permissions' => 'App\Model\Admin\Permissions',
            'Roles' => 'App\Model\Admin\Roles',
            'User' => 'App\Model\Admin\User',
            'Category' => 'App\Model\Api\Category',
            'Video' => 'App\Model\Api\Video',
        ];
    }

    /**
     * 返回对象 / 类的相关路径
     * @param string $type  ClassStat中对应的key
     * @param array $supportedClass   ClassStat：list
     * @param array $params    需要传递给对象的参数
     * @param bool $needInstance    需要返回对象 还是 类的命名空间
     * @return bool|mixed|object
     * @throws \ReflectionException
     */
    public static function initClass($type, $supportedClass, $params = [], $needInstance = true)
    {
        if(!array_key_exists($type, $supportedClass)) {
            return false;
        }
        $className = $supportedClass[$type];

        return $needInstance ? (new \ReflectionClass($className))->newInstanceArgs($params): $className;
    }

}