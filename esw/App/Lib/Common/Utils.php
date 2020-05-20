<?php

/** 公用函数库 */

if (!function_exists('p')) {
    /** 断点调试 */
    function p($a)
    {
        echo '<pre>';
        print_r($a);
        echo '</pre>';
        exit();
    }
}

if (!function_exists('getOnlyKey')) {
    /** 生成的唯一性key */
    function getOnlyKey($str)
    {
        return substr(md5(makeRandomString() . $str . time() . rand(0, 9999)), 8, 16);
    }
}

if (!function_exists('makeRandomString')) {
    /**
     * 生成随机字符串
     * @param int $length 长度
     * @return string 生成的随机字符串
     */
    function makeRandomString($length = 1)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }
}

if (!function_exists('isMobileNum')) {
    /** 是否是手机号 */
    function isMobileNum($v)
    {
        $search = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
        if (preg_match($search, $v)) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('checkEmail')) {
    /** 检查邮箱格式 */
    function checkEmail($email)
    {
        $pattern = "/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/";
        if (preg_match($pattern, $email)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('encodeData')) {
    /** base64编码 加盐 */
    function base64EncodeData($data)
    {
        $data = base64_encode("Y.{$data}M");
        return $data;
    }
}

if (!function_exists('decodeData')) {
    /** base64解码 */
    function base64DecodeData($data)
    {
        $data = base64_decode($data);
        $data = substr($data, 2, -1);
        return $data;
    }
}

if (!function_exists('handleTreeList')) {
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
    function handleTreeList($arr, $pid = 0, $depth = 0, $p_sub = 'parent_id', $c_sub = 'children', $d_sub = 'depth')
    {
        $returnArray = [];
        if (is_array($arr) && $arr) {
            foreach ($arr as $k => $v) {
                if ($v[$p_sub] == $pid) {
                    $v[$d_sub] = $depth;
                    $tempInfo = $v;
                    unset($arr[$k]); // 减少数组长度，提高递归的效率，否则数组很大时肯定会变慢
                    $temp = handleTreeList($arr, $v['id'], $depth + 1, $p_sub, $c_sub, $d_sub);
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

if (!function_exists('formatBytes')) {
    /**
     * 字节->兆转换
     * 字节格式化
     * @param $bytes
     * @return string
     */
    function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = round($bytes / 1073741824 * 100) / 100 . 'GB';
        } elseif ($bytes >= 1048576) {
            $bytes = round($bytes / 1048576 * 100) / 100 . 'MB';
        } elseif ($bytes >= 1024) {
            $bytes = round($bytes / 1024 * 100) / 100 . 'KB';
        } else {
            $bytes = $bytes . 'Bytes';
        }
        return $bytes;
    }
}

if (!function_exists('durationFormat')) {
    /**
     * 时间格式化，格式化秒
     * @param $number
     * @return string
     */
    function durationFormat($number)
    {
        if (!$number) {
            return '0分钟';
        }
        $newTime = '';
        if (floor($number / 3600) > 0) {
            $newTime .= floor($number / 3600) . '小时';
            $number = $number % 3600;
        }
        if ($number / 60 > 0) {
            $newTime .= floor($number / 60) . '分钟';
            $number = $number % 60;
        }
        if ($number < 60) {
            $newTime .= $number . '秒';
        }
        return $newTime;
    }
}

if (!function_exists('array2object')) {
    /** 数组转对象 */
    function array2object($arr)
    {
        if (!is_array($arr)) return;
        foreach ($arr as $k => $v) {
            if (is_array($v) || is_object($v))
                $arr[$k] = (object)array2object($v);
        }
        return (object)$arr;
    }
}
if (!function_exists('object2array')) {
    /** 对象转数组 */
    function object2array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (is_resource($v)) return;
            if (is_object($v) || is_array($v))
                $obj[$k] = (array)object2array($v);
        }
        return $obj;
    }
}

if (!function_exists('get_rand')) {
    /**
     * 生成随机数
     * @param int $len  长度
     * @param int $type 类型  0数字英文都要默认的，1，只要数字，2只要英文
     * @return string
     */
    function get_rand($len = 32, $type = 0)
    {
        $codes = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $card = "";

        $max = $type == 1 ? 9 : strlen($codes) - 1;
        $min = $type == 2 ? 10 : 0;

        for ($j = 0; $j < $len; $j++) {
            mt_srand((double)microtime() * 1000000 * 100000 + $j);
            $randval = mt_rand($min, $max);
            $card .= $codes[$randval];
        }

        return $card;
    }
}

if (!function_exists('getcontentpic')) {
    /** 提取文章内容中的图片
     * @param string $content
     * @return string
     */
    function getcontentpic($content)
    {
        $pic = '';
        $content = stripslashes($content);
        $content = preg_replace("/\<img src=\".*?image\/face\/(.+?).gif\".*?\>\s*/is", '', $content);    //移除表情符
        preg_match("/src\=[\"\']*([^\>\s]{25,105})\.(jpg|gif|png)/i", $content, $mathes);
        if (!empty($mathes[1]) || !empty($mathes[2])) {
            $pic = "{$mathes[1]}.{$mathes[2]}";
        }
        return addslashes($pic);
    }
}

if (!function_exists('changeTimeType')) {
    /** 以秒计算时长：hh:mm:ss [处理倒计时] */
    function changeTimeType($seconds)
    {
        if ($seconds > 3600) {
            $hours = intval($seconds / 3600);
            $minutes = $seconds % 3600;
            $time = $hours . ":" . gmstrftime('%M:%S', $minutes);
        } else {
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }
}

if (!function_exists('time_tran')) {
    /** 获取发布时间与当前时间差 */
    function time_tran($the_time)
    {
        $now_time = time();
        $dur = $now_time - $the_time;
        if ($dur < 0) {
            return $the_time;
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        //return floor($dur / 86400) . '天前';
                        return date('Y-m-d H:i', $the_time);
                    }
                }
            }
        }
    }
}

if (!function_exists('getTimeDiff')) {
    /** 计算两个时间差 */
    function getTimeDiff($begin_time, $end_time)
    {
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
        return $days . '天' . $hours . '小时' . $mins . '分';
    }
}

if (!function_exists('get_client_ip')) {
    /** 得到客户端的IP */
    function get_client_ip()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }
}

if (!function_exists('get_str')) {
    /** 对字符串进行过滤 */
    function get_str($val, $def = '')
    {
        return input($val, $def, 'htmlspecialchars');
    }
}

if (!function_exists('formatCount')) {
    /**
     * 格式化数量
     * @param $count
     * @param string $delimiter
     * @return float|int|string
     */
    function formatCount($count, $delimiter = '')
    {
        if ($count < 1000) return $count;
        $count = $count / 1000;
        $units = array('千+', '万+', '十万+', '百万+', '千万+');

        for ($i = 0; $count >= 10 && $i < 5; $i++) $count /= 10;

        return round($count, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('list_sort_by')) {
    /**
     * list_sort_by()对查询结果集进行排序
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param string $sortby 排序类型：asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }
}

if (!function_exists('list_to_tree2')) {
    /**
     * 将数组转为父子型属性结构数组
     * @param array $list
     * @param string $id
     * @param string $pid
     * @param string $child
     * @return array
     */
    function list_to_tree2($list, $id = 'id', $pid = "pid", $child = "_child", $tree = array())
    {
        if (is_array($list)) {
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$id]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
            // 判断是否存在parent
                $parentid = $data[$pid];
                if (0 == $parentid) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentid])) {
                        $parent =& $refer[$parentid];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}

if (!function_exists('tree_to_list2')) {
    /**
     * tree_to_list2()将父子型结构数组还原成正常数组
     * @param array $tree
     * @param string $child
     * @param string $order
     * @param array $list
     * @return array
     */
    function tree_to_list2($tree, $child = '_child', $order = 'id', &$list = array())
    {
        if (is_array($tree)) {
            $refer = array();
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = list_sort_by($list, $order, $sortby = 'asc');
        }
        return $list;
    }
}

if (!function_exists('getTree')) {
    /** 无极限树 */
    function getTree($list, $pid = 0)
    {
        static $tree = array();
        foreach ($list as $row) {
            if ($row['pid'] == $pid) {
                $tree[] = $row;
                getTree($list, $row['id']);
            }
            unset($row);
        }
        return $tree;
    }
}

if (!function_exists('delDirAndFile')) {
    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param str $path 待删除目录路径
     * @param bool $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
     * @return bool 返回删除状态
     */
    function delDirAndFile($path, $delDir = FALSE)
    {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        } else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
    }
}

if (!function_exists('arrToOne')) {
    /** 多维数组变成一维数组 */
    function arrToOne($multi)
    {
        $arr = array();
        foreach ($multi as $key => $val) {
            if (is_array($val)) {
                $arr = array_merge($arr, arrToOne($val));
            } else {
                $arr[] = $val;
            }
        }
        return $arr;
    }
}

if (!function_exists('$array2D')) {
    /** 二维数组去除重复 */
    function array_unique_fb($array2D)
    {
        foreach ($array2D as $v) {
            $v = join(',', $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[] = $v;
        }
        $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v) {
            $temp[$k] = explode(',', $v); //再将拆开的数组重新组装
        }
        return $temp;
    }
}

if (!function_exists('injCheck')) {
    /** SQL注入字符检测 */
    function injCheck($sql_str)
    {
        $check = preg_match('/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/', $sql_str);
        if ($check) {
            echo '非法字符！！';
            exit;
        } else {
            return $sql_str;
        }
    }
}

if (!function_exists('format')) {
    /** 去除合同富文本标签 */
    function format($data)
    {
        $content_01 = $data;//从数据库获取富文本content
        $content_02 = htmlspecialchars_decode($content_01);//把一些预定义的 HTML 实体转换为字符
        $content_03 = str_replace("&nbsp;", "", $content_02);//将空格替换成空
        $content_04 = str_replace("</p>", "</p>\n", $content_03);
        $contents = strip_tags($content_04);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
        return $contents;
    }
}

if (!function_exists('html_substr_content')) {
    /** 将富文本中文字截取其中的一部分  */
    function html_substr_content($content, $length = 100)
    {
        $content = htmlspecialchars_decode($content);      //把一些预定义的 HTML 实体转换为字符
        $content = str_replace(" ", "", $content);     //将空格替换成空
        $content = strip_tags($content);                 //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
        $con = mb_substr($content, 0, $length, "utf-8");   //返回字符串中的前100字符串长度的字符
        return $con;
    }
}

if (!function_exists('generate_out_trade_no')) {
    /** 生产唯一订单号 */
    function generate_out_trade_no()
    {
        $a = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        return 'F_' . $a . rand(10000000, 99999999);
    }
}

if (!function_exists('get_age')) {
    /**
     * 计算.年龄
     * @param int $mydate 年份
     * @return str
     */
    function get_age($mydate)
    {
        $birth = $mydate;
        list($by, $bm, $bd) = explode('-', $birth);
        $cm = date('n');
        $cd = date('j');
        $age = date('Y') - $by - 1;
        if ($cm > $bm || $cm == $bm && $cd > $bd) {
            $age++;
        }

        return $age;
    }
}

if (!function_exists('is_date')) {
    /** 判断日期格式 */
    function is_date($str, $format = "Y-m-d")
    {
        $strArr = explode("-", $str);
        if (empty($strArr)) {
            return false;
        }
        foreach ($strArr as $val) {
            if (strlen($val) < 2) {
                $val = "0" . $val;
            }
            $newArr[] = $val;
        }
        $str = implode("-", $newArr);
        $unixTime = strtotime($str);
        $checkDate = date($format, $unixTime);
        if ($checkDate == $str) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('my_strlen')) {
    /** 兼容中文的得到字符串长度 */
    function my_strlen($val)
    {
        return mb_strlen($val, 'UTF-8');
    }
}

if (!function_exists('cut_str')) {
    /**
     *  *Utf-8、gb2312都支持的汉字截取函数
     *  *编码默认为utf-8
     *  *开始长度默认为0
     *  *@param    $string 字符串
     *  *@param    $sublen 截取长度
     *  *@paramint  $start 开始长度
     *  *@paramstring$code  编码
     *  *@returnstring
     *  */
    function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);

            if (count($t_string[0]) - $start > $sublen) {
                return join('', array_slice($t_string[0], $start, $sublen)) . "...";
            }

            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';

            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr .= substr($string, $i, 2);
                    } else {
                        $tmpstr .= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129) {
                    $i++;
                }
            }
            if (strlen($tmpstr) < $strlen) {
                $tmpstr .= "...";
            }

            return $tmpstr;
        }
    }
}

if (!function_exists('friendly_date')) {
    /** 时间展示 */
    function friendly_date($sTime, $type = 'normal', $alt = 'false')
    {
        if (!$sTime) return '';
        //sTime=源时间，cTime=当前时间，dTime=时间差

        $cTime = time();
        $dTime = $cTime - $sTime;
        $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
        $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));

        //normal：n秒前，n分钟前，n小时前，日期
        switch ($type) {
            case 'normal':
                if ($dTime < 60) {
                    if ($dTime < 10) {
                        return '刚刚';
                    } else {
                        return intval(floor($dTime / 10) * 10) . "秒前";
                    }
                } elseif ($dTime < 3600) {
                    return intval($dTime / 60) . "分钟前";
                    //今天的数据.年份相同.日期相同.
                } elseif ($dYear == 0 && $dDay == 0) {
                    //return intval($dTime/3600)."小时前";
                    return '今天' . date('H:i', $sTime);
                } elseif ($dYear == 0) {
                    return date("m月d日 H:i", $sTime);
                } else {
                    return date("Y-m-d H:i", $sTime);
                }
                break;
            case 'mohu':
                if ($dTime < 60) {
                    return $dTime . "秒前";
                } elseif ($dTime < 3600) {
                    return intval($dTime / 60) . "分钟前";
                } elseif ($dTime >= 3600 && $dDay == 0) {
                    return intval($dTime / 3600) . "小时前";
                } elseif ($dDay > 0 && $dDay <= 7) {
                    return intval($dDay) . "天前";
                } elseif ($dDay > 7 && $dDay <= 30) {
                    return intval($dDay / 7) . '周前';
                } elseif ($dDay > 30) {
                    return intval($dDay / 30) . '个月前';
                }
                break;
            case 'full':
                return date("Y-m-d , H:i:s", $sTime);
                break;
            case 'ymd':
                return date("Y-m-d", $sTime);
                break;
            default:
                if ($dTime < 60) {
                    return $dTime . "秒前";
                } elseif ($dTime < 3600) {
                    return intval($dTime / 60) . "分钟前";
                } elseif ($dTime >= 3600 && $dDay == 0) {
                    return intval($dTime / 3600) . "小时前";
                } elseif ($dYear == 0) {
                    return date("Y-m-d H:i:s", $sTime);
                } else {
                    return date("Y-m-d H:i:s", $sTime);
                }
                break;
        }
    }
}

if (!function_exists('download')) {
    /**
     * PHP强制下载文件 不想让浏览器直接打开文件，如PDF文件，而是直接下载文件
     * 使用方法： download('/down/test_45f73e852.zip');
     * @param $filename
     */
    function download($filename)
    {
        if ((isset($filename)) && (file_exists($filename))) {
            header("Content-length: " . filesize($filename));
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile("$filename");
        } else {
            echo "Looks like file does not exist!";
        }
    }
}

if (!function_exists('message')) {
    /**
     * PHP页面提示与跳转
     * 我们在进行表单操作时，有时为了友好需要提示用户操作结果，并跳转到相关页面，请看以下函数：
     * 使用方法如下：message('操作提示','操作成功！','http://www.helloweba.com/');
     * @param $msgTitle
     * @param $message
     * @param $jumpUrl
     */
    function message($msgTitle, $message, $jumpUrl)
    {
        $str = '<!DOCTYPE HTML>';
        $str .= '<html>';
        $str .= '<head>';
        $str .= '<meta charset="utf-8">';
        $str .= '<title>页面提示</title>';
        $str .= '<style type="text/css">';
        $str .= '*{margin:0; padding:0}a{color:#369; text-decoration:none;}a:hover{text-decoration:underline}body{height:100%; font:12px/18px Tahoma, Arial,  sans-serif; color:#424242; background:#fff}.message{width:450px; height:120px; margin:16% auto; border:1px solid #99b1c4; background:#ecf7fb}.message h3{height:28px; line-height:28px; background:#2c91c6; text-align:center; color:#fff; font-size:14px}.msg_txt{padding:10px; margin-top:8px}.msg_txt h4{line-height:26px; font-size:14px}.msg_txt h4.red{color:#f30}.msg_txt p{line-height:22px}';
        $str .= '</style>';
        $str .= '</head>';
        $str .= '<body>';
        $str .= '<div>';
        $str .= '<h3>' . $msgTitle . '</h3>';
        $str .= '<div>';
        $str .= '<h4>' . $message . '</h4>';
        $str .= '<p>系统将在 <span style="color:blue;font-weight:bold">3</span> 秒后自动跳转,如果不想等待,直接点击 <a href="{$jumpUrl}">这里</a> 跳转</p>';
        $str .= "<script>setTimeout('location.replace(\'" . $jumpUrl . "\')',2000)</script>";
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</body>';
        $str .= '</html>';
        echo $str;
    }
}

if (!function_exists('shieldtext')) {
    /**  php屏蔽关键字 */
    function shieldtext($str, $file)
    {
        $sensitive_word = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . $file);
        $arr = explode('|', $sensitive_word);

        foreach ($arr as $val) {
            $leng = strlen($val);
            $newArr[$leng][] = $val;
        }
        $count = count($arr);
        $tmp = array_keys($newArr);
        rsort($tmp);
        for ($i = 0; $i <= 3; $i++) {
            foreach ($tmp as $v) {
                foreach ($newArr[$v] as $v1) {
                    $str = str_replace($v1, '**', $str);
                }
            }
        }
        return $str;
    }
}
/*
//第三方登录
function qq_login()
{
    Vendor('QQconnect.API.qqConnectAPI');
// require_once("../../API/qqConnectAPI.php");
    $qc = new QC();
    $qc->qq_login();
}

//百度翻译接口
function translate($str)
{
    Vendor('mobile.Translate');
    $t = new \Translate();
    $reslut = $t->exec($str, $from = 'zh', $to = 'en');
    return $reslut;
}
*/