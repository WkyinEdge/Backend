<?php


namespace App\HttpController\Api;

use App\Lib\ClassArr;
use App\Lib\Upload\IntoUpload;
use EasySwoole\Http\Message\Status;

/**
 * 文件上传类（测试用，若其他控制器需要用到上传功能时去调用：Upload\IntoUpload）
 * Class Upload
 * @package App\HttpController\Api
 */
class Upload extends ApiBase
{

    /**
     * 测试上传
     * @return bool
     * @throws \Exception
     */
    public function up(){
        $request = $this->request();
        //var_dump($request);
        return $this->writeJson(200,'ok',IntoUpload::uploadFile($request));
    }
    /**
     * 文件上传逻辑代码1
     */
    public function file(){
        $request = $this->request();
        $files = $request->getSwooleRequest()->files;
        /*var_dump($request->decryptParams);
        var_dump($files);exit;*/
        $types = array_keys($files);

        //$type = $types[0];    //单个文件上传
        /* 多文件上传 */
        $urls = [];
        for ($i = 0; $i<count($types); $i++){
            $type = $types[$i];
            $url = $this->uploadfiles($type, $request);
            if(!is_string($url)) $this->response()->end();
            $urls['url'] = $url;
        }
        //var_dump($urls);
        return $this->writeJson(200,'OK',$urls);
        /*原始文件上传逻辑
            $videos = $request->getUploadedFile('file');
            $flag = $videos->moveTo('/www/admin/192.168.253.128_80/wwwroot/cs1.mp4');
            $data = [
                'url' => '/cs1.mp4',
                'flag' => $flag
            ];
            if($flag) {
                return $this->writeJson(200,'上传成功', $data);
            }else {
                return $this->writeJson(400,'上传错误');
            }
        */
    }

    /**
     * 文件上传逻辑处理代码2
     * @param $type 文件类型
     * @param $request 请求对象
     * @return bool
     */
    private function uploadfiles($type, $request) {
        /*
         *  一种很陋的写法（不利于维护）
            if($type === 'video'){
                $classPath = '\App\Lib\Upload\MediaUpload';
            }else if($type === 'image'){
                $classPath = '\App\Lib\Upload\ImageUpload';
            }
            //$classPath = '\App\Lib\Upload\' . ucfirst($type);//另一种很陋的写法：转换首字母大写;
            //$upload_obj = new $classPath($request);
        */
        /* 新的写法：利用php反射机制 */
        if (empty($type)){
            return $this->writeJson(400, '上传文件不合法');
        }
        //$classArr = new ClassArr();
        $uploadClassPath = ClassArr::uploadClassStat();

        try{
            $upload_obj = ClassArr::initClass($type, $uploadClassPath, [$request, $type]);
            $api_file = $upload_obj->upload();


        }catch (\Exception $e){
            //return $this->writeJson(400, $e->getMessage(), []);
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),Status::CODE_BAD_REQUEST);
        }

        if (empty($api_file)){
            return $this->writeJson(400, '上传失败', []);
        }

        return $api_file;
    }



}