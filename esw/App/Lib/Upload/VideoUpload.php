<?php


namespace App\Lib\Upload;


class VideoUpload extends BaseUpload
{
    /**
     * 上传文件类型
     * @var string
     */
    public $fileType = 'video';

    public $maxSize = 122;

    public $fileExtTypes = [
        'mp4',
        'x-flv',
        'avi',
    ];


}