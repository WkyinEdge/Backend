<?php


namespace App\Lib\Cache;

use App\Lib\Utils;
use App\Model\Video;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\FastCache\Cache;

class CrontabCache extends AbstractCronTask
{
    public static function getRule(): string
    {
        /**
            @yearly                    每年一次 等同于(0 0 1 1 *)
            @annually                  每年一次 等同于(0 0 1 1 *)
            @monthly                   每月一次 等同于(0 0 1 * *)
            @weekly                    每周一次 等同于(0 0 * * 0)
            @daily                     每日一次 等同于(0 0 * * *)
            @hourly                    每小时一次 等同于(0 * * * *)
            '1-59/2 * * * *'           奇数分钟
            '0-58/2 * * * *'           偶数分钟
         */
        return '@hourly';//'*/10 * * * *'; //;;
    }

    public static function getTaskName(): string
    {
        return  'CrontabCache';
    }

    function run(int $taskId, int $workerIndex)
    {
        //var_dump('c');
        /*TaskManager::getInstance()->async(function (){
            var_dump('r');
        });*/
        self::setIndexVideo();
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        echo $throwable->getMessage();
    }

    /**
     * 定时静态化数据库数据到服务器本地文件的处理逻辑
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public static function setIndexVideo() {

        $catIds = array_keys(\Yaconf::get('es_category.cats'));
        array_unshift($catIds, 0);

        $model = new Video();

        foreach ($catIds as $catid){
            $condition = [];
            if(!empty($catid)){ //高明之处：如果为0就不加限制条件获取所有数据
                $condition['cat_id'] = $catid;
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

            /* 保存文件形式 */
             //$flag = file_put_contents(Utils::getStaticJsonPath().'/'.$catid.'.json', json_encode($data));

            /* 缓存形式 */
            $flag = Cache::getInstance()->set('index_video_data_cat_id'.$catid, $data);

            if(empty($flag)){
                // 报警
                echo "cat_id:".$catid.' put data error' . PHP_EOL;
            }
             else echo "cat_id:".$catid.' put data success' . PHP_EOL;

        }
    }



}