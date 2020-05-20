<?php


namespace App\Lib\Upload;


class MediaUpload extends BaseUpload
{
    /**
     * 上传文件类型
     * @var string
     */
    public $fileType = 'media';

    public $maxSize = 122;

    public $fileExtTypes = [
        'mp4',
        'x-flv',
        'avi',
        'mp3'
    ];


}