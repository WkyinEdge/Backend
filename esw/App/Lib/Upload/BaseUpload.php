<?php


namespace App\Lib\Upload;

use App\Lib\Utils;

class BaseUpload
{
    protected $request;
    protected $type;
    protected $size;
    protected $fileName;
    protected $clientMediaType;
    protected $api_file;

    public function __construct($request,$type = null)
    {
        $this->request = $request;
        if(empty($type)){
            $files = $this->request->getSwooleRequest()->files;
            $types = array_keys($files);
            $this->type = $types[0];
        }else{
            $this->type = $type;
        }

        //print_r($this->type);
    }

    public function upload() {
        /* 需要与前端约定：上传文件的key值 要和后端设置的保持一致 */
        if ($this->type != $this->fileType){
            return false;
        }else{
            /* 获取上传文件，返回ES中的uploadedFile对象 */
            $objs = $this->request->getUploadedFile($this->type);

            $this->size = $objs->getSize();
            $this->checkSize();

            $this->fileName = $objs->getClientFileName();

            $this->clientMediaType = $objs->getClientMediaType();

            $this->checkMediaType();
            print_r($this->clientMediaType);
            $file = $this->getFile();

            $flag = $objs->moveTo($file);

            //var_dump($this->api_file);
            if (!empty($flag)){

                return $this->api_file;
            }
            return false;

        }
    }

    public function getFile() {
        $fileName = $this->fileName;
        $pathinfo = pathinfo($fileName);
        $extension = $pathinfo['extension'];

        $dirname = '/upload/' . $this->type . '/'. date('Y') . date('M');
        //$dir = EASYSWOOLE_ROOT . '/webroot' . $dirname;
        $dir = \Yaconf::get('wwwroot_path')['path'] . $dirname;
        if (!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        $basename = '/' . Utils::getOnlyKey($fileName) . '.' . $extension;
        //print_r($dir);
        /* 前端用的相对路径 */
        $this->api_file = $dirname . $basename;
        /* 我们用来存储的绝对路径 */
        return $dir . $basename;


    }

    /**文件类型检测
     * @throws \Exception
     */
    public function checkMediaType(){
        $clientMediaType = explode('/', $this->clientMediaType);
        $clientMediaType = $clientMediaType[1] ?? '';
        if(empty($clientMediaType)){
            throw new \Exception("上传{$this->type}文件不合法");
        }
        if (!in_array($clientMediaType, $this->fileExtTypes)){
            throw new \Exception("上传{$this->type}文件类型错误");
        }
    }

    /**文件大小检测
     * @return bool
     */
    public function checkSize() {
        if (empty($this->size)){
            throw new \Exception("不能上传空文件");
        };

        /* 上传文件的大小限制 */
        $sizeM = $this->size/1024/1024; // 单位转换为MB
        if($sizeM > $this->maxSize){
            throw new \Exception("上传文件大小不能超过{$this->maxSize}M");
        }
    }







}