<?php


namespace App\Lib\Cache;

use App\Lib\Utils;
use App\Model\Api\Category;
use App\Model\Api\Video;
use EasySwoole\Component\Di;
use EasySwoole\FastCache\Cache;

class ApiCache
{
    /**
     * 缓存主页接口数据
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public static function setIndexVideo() {

        $catIds = Category::create()->column('id');
        //$catIds = array_keys(\Yaconf::get('es_category.cats'));
        array_unshift($catIds, 0);

        $cacheType = \Yaconf::get('es_conf.base.indexCacheType');

        $model = new Video();

        foreach ($catIds as $catid){
            $condition = [];
            if(!empty($catid)){ //高明之处：如果为0就不加限制条件获取所有数据
                $condition['category_id'] = $catid;
            }

            try {
                $data = $model->getVideoCachedata($condition);
            }catch (\Exception $e){
                // 报警 ：短信 / 邮件
                $data = [];
            }
            if(empty($data)) {
               continue;
            }


            foreach ($data as &$list){
                $list['create_time'] = date('Ymd H:i:s',$list['create_time']);
                // 如果接入云平台，还需要把视频时长，用秒数转为：H:M:S的格式 gmstrftime("%H:%M:%S")
            }

            switch ($cacheType){
                /* 保存文件形式 */
                case 'File':
                    $flag = file_put_contents(Utils::getStaticJsonPath() . '/' .$catid.'.json', json_encode($data));
                    break;
                /* FastCache缓存形式 */
                case 'FastCache':
                    $flag = Cache::getInstance()->set('index_video_data_cat_id'.$catid, $data);
                    break;
                /* Redis缓存形式 */
                case 'Redis':
                    $flag = Di::getInstance()->get('REDIS')->set('index_video_data_cat_id'.$catid, $data);
                    break;
                default:
                    throw new \Exception('请稍后再试');
                    break;
            }

            if(empty($flag)){
                // 写日志 / 报警
                echo "cat_id:".$catid.' put data error: ' .$cacheType . PHP_EOL;
            }
             else echo "cat_id:".$catid.' put data success: ' .$cacheType . PHP_EOL;

        }
    }

    public static function getCache($catId = 0) {

        $cacheType = \Yaconf::get('es_conf.base.indexCacheType');

        switch ($cacheType){
            /* 保存文件形式 */
            case 'File':
                $videoFile = Utils::getStaticJsonPath() . '/' . $catId . '.json';
                $videoData = is_file($videoFile) ? file_get_contents($videoFile) : [];
                $videoData = !empty($videoData) ? json_decode($videoData, true) : [];
                break;
            /* FastCache缓存形式 */
            case 'FastCache':
                $videoData = Cache::getInstance()->get('index_video_data_cat_id'.$catId);
                $videoData = !empty($videoData) ? $videoData : [];
                break;
            /* Redis缓存形式 */
            case 'Redis':
                $videoData = Di::getInstance()->get('REDIS')->get('index_video_data_cat_id'.$catId);
                $videoData = !empty($videoData) ? json_decode($videoData, true) : [];
                break;
            default:
                throw new \Exception('请求不合法');
                break;
        }
        return $videoData;
    }



}