<?php
namespace App\Lib\Auth;

/**
 * aes 加密 解密类库
 * @by singwa
 * Class Aes
 * @package app\common\lib
 */
class Aes {

    /**
     * 获取配置文件AES公钥key
     * @return mixed
     */
    private static function getKey(){
        return \Yaconf::get('es_conf.auth.aeskey');
    }

    /**
     * 加密
     * @param String input 加密的字符串
     * @param String key   解密的key
     * @return HexString
     */
    public static function encrypt($input = '')
    {
        $key = self::getKey();
        // des加密并会自动base64_encode方便传输  解密需先base64_decode 再解密
        //$data = openssl_encrypt($input,'des-ede3',$key,0);

        //AES加密 需要手动base64_encode 同样：解密需先base64_decode 再解密
        $data = openssl_encrypt($input, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = base64_encode($data);

        return $data;
    }

    /**
     * 解密
     * @param String input 解密的字符串
     * @param String key   解密的key
     * @return String
     */
    public static function decrypt($sStr)
    {
        $key = self::getKey();

        $sStr = base64_decode($sStr);
        // des方式解密
        //$decrypted = openssl_decrypt($sStr,'des-ede3',$this->key,OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        // AES方式解密
        $decrypted = openssl_decrypt($sStr,'AES-128-ECB',$key,OPENSSL_RAW_DATA);
        return $decrypted;
    }

}