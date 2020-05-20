<?php


namespace App\Lib\Upload;

use App\Lib\ClassArr;
use EasySwoole\Http\Request;

/**
 * 文件上传到服务器的入口类
 * Class IntoUpload
 * @package App\Lib\Upload
 */
class IntoUpload
{
    /**
     * 文件上传API，返回保存的相对路径数组
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public static function uploadFile(Request $request){

        $files = $request->getSwooleRequest()->files;
        $types = array_keys($files);
        //$type = $types[0];    //单个文件上传
        /* 多文件上传 */
        $urls = [];
        for ($i = 0; $i<count($types); $i++){
            $type = $types[$i];
            $url = self::uploadfiles($type, $request);
            $urls[ 'url'] = $url;
        }
        return $urls;
    }

    /**
     * 文件上传逻辑处理代码
     * @param $type 文件类型
     * @param $request
     * @return mixed 返回文件保存的相对路径
     * @throws \Exception
     */
    private static function uploadfiles($type, $request) {

        /* 新的写法：利用php反射机制 */
        if (empty($type)){
            throw new \Exception('上传文件不合法',400);
        }
        //$classArr = new ClassArr();
        $uploadClassPath = ClassArr::uploadClassStat();

        try{
            $upload_obj = ClassArr::initClass($type, $uploadClassPath, [$request, $type]);
            $api_file = $upload_obj->upload();

        }catch (\Exception $e){
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }

        if (empty($api_file)){
            throw new \Exception('上传失败',400);
        }

        return $api_file;
    }


}