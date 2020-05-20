<?php
namespace App\Lib;

use EasySwoole\Http\Response;

/**
 * 通用工具类
 */
class Utils {

    /**
     * 重写writeJson方法
     * @param Response $response
     * @param int $statusCode
     * @param null $message
     * @param null $result
     * @return bool
     */
    public static function writeJson(Response $response,$statusCode = 200, $message = null, $result = null)
    {
        if (!$response->isEndResponse()) {
            $data = Array(
                "code" => $statusCode,
                "msg" => $message,
                "result" => $result
            );
            $response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $response->withHeader('Content-type', 'application/json;charset=utf-8');
            $response->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取13位时间戳，为什么要13位？
     * 因为正常10位的时间戳以秒为单位计时，请求量大的情况下可能会重复，13位以ms为单位，唯一性强
     * @return int
     */
    public static function get13TimeStamp() {
        list($t1, $t2) = explode(' ', microtime());
        return $t2 . ceil($t1 * 1000);
    }

	/**
     * 生成的唯一性key
     * @param string $str
     * @return string 
    */
    public static function getOnlyKey($str = '') {
        return substr(md5(self::makeRandomString() . $str . time() . rand(0, 9999)), 8, 16);
    }

    /**
     * 是否是手机号
     * @param $v
     * @return bool
     */
    public static function isMobileNum($v)
    {
        $search = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
        if (preg_match($search, $v)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 编码 加盐
     * @param mixed $data
     * @access public
     * @return void base64EncodeData
     */
    public static function base64EncodeData($data)
    {
        $data = base64_encode("Y.{$data}M");
        return $data;
    }

    /**
     * 解码uid
     * @param mixed $data
     * @access public
     * @return void base64DecodeData
     */
    public static function base64DecodeData($data)
    {
        $data = base64_decode($data);
        $data = substr($data, 2, -1);
        return $data;
    }

    /**
     * 生成随机字符串
     * @param int $length 长度
     * @return string 生成的随机字符串
     */
    public static function makeRandomString($length = 2) {
  		
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for($i=0; $i<$length; $i++) {
            $str .= $strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
  }

    /**
     * 获取静态化Json文件目录
     * @return string
     */
    public static function getStaticJsonPath() {
        $dir = EASYSWOOLE_ROOT .'/webroot/video/json';
        if (!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /**
     * handleTreeList
     * 建立数组树结构列表
     * @param $arr 数组
     * @param int $pid 父级id
     * @param int $depth 增加深度标识
     * @param string $p_sub 父级别名
     * @param string $d_sub 深度别名
     * @param string $c_sub 子集别名
     * @return array
     */
    public static function handleTreeList($arr,$pid=0,$depth=0,$p_sub='parent_id',$c_sub='children',$d_sub='depth')
    {
        $returnArray = [];
        if(is_array($arr) && $arr) {
            foreach($arr as $k => $v) {
                if($v[$p_sub] == $pid) {
                    $v[$d_sub] = $depth;
                    $tempInfo = $v;
                    unset($arr[$k]); // 减少数组长度，提高递归的效率，否则数组很大时肯定会变慢
                    $temp = self::handleTreeList($arr,$v['id'],$depth+1,$p_sub,$c_sub,$d_sub);
                    if ($temp) {
                        $tempInfo[$c_sub] = $temp;
                    }
                    $returnArray[] = $tempInfo;
                }
            }
        }
        return $returnArray;
    }
}