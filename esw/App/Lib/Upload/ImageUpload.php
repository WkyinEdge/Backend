<?php


namespace App\Lib\Upload;


class ImageUpload extends BaseUpload
{
    /**
     * 上传文件类型
     * @var string
     */
    public $fileType = 'image';

    public $maxSize = 2;

    public $fileExtTypes = [
        'png',
        'jpg',
        'jpeg'
    ];


}