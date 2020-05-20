<?php


namespace App\HttpController\Api;


//use App\Lib\Redis\Redis;
use App\Model\Api\Video as VideoModel;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\Redis;
use EasySwoole\Validate\Validate;
use EasySwoole\EasySwoole\Task\TaskManager;

class Video extends ApiBase
{
    /**当前控制器需要校验的API 以及对应的规则
     * @param string|null $action
     * @return Validate|null
     */
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action){
            case 'add':{
                $v->addColumn('name','名称')->required('不能为空')
                    ->lengthMax(20,'长度不能超过20')
                    ->lengthMin(2,'长度不能小于2');
                $v->addColumn('url','网址')->required('不能为空');
                $v->addColumn('image','图片')->required('不能为空');
                $v->addColumn('content','内容')->required('不能为空');
                $v->addColumn('cat_id','栏目ID')->required('不能为空')
                    ->numeric('必须是数字')
                    ->lengthMax(5,'长度不能超过5');
                //$v->addColumn('status','状态')->required('不能为空');
                //$v->addColumn('type','类型')->required('不能为空');
                //$v->addColumn('uploader','上传者')->required('不能为空');
                break;
            }
        }
        return $v;
    }


    public $logType = 'video';



    /**
     * 视频数据入库
     * @return bool
     */
    public function add()
    {
        $params = $this->request()->getRequestParam();
        Logger::getInstance()->log($this->logType . 'add:' . json_encode($params));
        $data = [
            'name' => $params['name'],
            'url'=> $params['url'],
            'image' => $params['image'],
            'content' => $params['content'],
            'cat_id' => $params['cat_id'],
            'create_time' => time(),
            'status' => \Yaconf::get('es_conf.status.normal'),//0 1 2
            'type' => 1,
            'uploader' => 'wky',
        ];
        //var_dump($data);
        try{
            $model = new VideoModel();
            $videoId = $model->add($data);
        } catch (\Exception $e) {
            //return $this->writeJson(Status::CODE_BAD_REQUEST, $e->getMessage());
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),Status::CODE_BAD_REQUEST);
        }

        if(!empty($videoId)){
            return $this->writeJson(Status::CODE_OK, 'OK',['id'=>$videoId]);
        }else{
            return $this->writeJson(Status::CODE_BAD_REQUEST,'上传失败',['id'=>null]);
        }

        return $this->writeJson(200,'ok',$data['params']);
    }

    public function getVideo(){
        $model = new VideoModel();
        $data = $model->all();
        return $this->writeJson(200,'查询成功',$data);
    }

    /**
     * 获取单个视频数据Api
     * @return bool
     */
    public function index()
    {
        /* 过滤非法字符 （非数字字符返回0） */
        $id = intval($this->params['id']);
        if(empty($id)){
            return $this->writeJson(Status::CODE_BAD_REQUEST, '请求不合法');
        }

        //获取视频基本信息
        try{
            $video = (new VideoModel())->getById($id);
        }catch (\Exception $e) {
            // $e->getmessage() 写入日志，不暴露给前端
            //return $this->writeJson(Status::CODE_BAD_REQUEST, '请求不合法');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),Status::CODE_BAD_REQUEST);
        }

        if(!$video || $video['status'] != \Yaconf::get('es_conf.status.normal')){
            return $this->writeJson(Status::CODE_BAD_REQUEST, '视频不存在');
        }

        /* 播放数 异步统计逻辑 */
            /* 投放Task 异步任务 */
        TaskManager::getInstance()->async(function () use ($id){
            $redis = Redis::getInstance()->get('redis')->defer();
            //总点击排行
            $redis->zInCrBy(\Yaconf::get('es_conf.redis.video_play_key'), 1, $id);
            //今日点击排行
            $redis->zInCrBy(\Yaconf::get('es_conf.redis.video_play_key') . date("ymd"), 1, $id);
        });

        return $this->writeJson(Status::CODE_OK, 'OK',$video);
    }

    /**
     * 排行榜接口，总排行榜 日排行榜 周排行
     * 交集命令：ZUNIONSTORE 新集合名 2（天数） programmer（集合1） manager（集合2）... WEIGHTS 1（集合1权重） 3（集合2权重）...
     * @return array
     */
    /**
     * 日排行 、周排行 、月排行...实现逻辑：
     * 存的时候，以key . date("ymd");的形式作为key值存储播放数：即为当日排行；
     * 统计周排行时，用循环的方式组织一个key值为：key . date("ymd", strtotime("* days ago"))的array，
     * （*）代表循环因子，一周就是7，月就是30...以次类推；
     * 然后用redis：ZUNIONSTORE 命令求交集保存为周排行集合、月排行集合...
     */
    public function rank(){

        $redisVideoPlayKey = \Yaconf::get('es_conf.redis.video_play_key');

        $period = $this->params['period'];

        // 需要什么排行，往里面加就行
        switch ($period) {
            case 0: // 总排行
                $res = $this->rankCompute($period, $redisVideoPlayKey);
                break;
            case 7: // 周排行
                $res = $this->rankCompute($period, $redisVideoPlayKey);
                break;
            case 30: // 月排行
                $res = $this->rankCompute($period, $redisVideoPlayKey);
                break;
            default:
                return $this->writeJson(Status::CODE_BAD_REQUEST, '请求不合法');
        }

        return $this->writeJson(Status::CODE_OK, 'OK', $res);
    }

    /**
     * 顺便扩展redis作为数据库使用的逻辑：
     * 首先必须要定义一个自增字段表示一个表的主键id，每个表都要保存一个独立的自增字段num表示当前行数，用incr(num)方法会返回自增后的条数为id
     * 一个表有了不重复的主键 接下来就用这个主键配合表名例如：user：id 表示用户表id行
     * 表的内容用hash 类型表示 ，一个哈希表示1行，有几个值，就代表有几列，这就完成一个简单但只能用主键id查询的表
     * （建议把id也存入进去）
     * 如果想增加其他索引，比如说不用id 改用name 来查询时，
     * 需要再提前定义一个user：name为key，id为value的行字段，用来获取id，再用id来获取整条hash行数据
     * 增删改查时，就可以用user：已知的id  操作对应的行数据
     *
     * 复杂的场景，比如关联查询：
     * 1对1：需要两个表相互保存对方主键，先用一个已知条件的查询另一个表的id，然后再查另一个表数据
     * 1对N：假设a：b两表，已知a表的id，然后要把b表所有行循环获取到，然后再循环找出外键 = a表已知id的行
     * N对M：太复杂了，除了n和m外还需要一个表用hash存储他们的主键，类似于下面的统计点赞功能
     */

    /**
     * 用redis做关系数据库，统计点赞数的方法：
     *
     * 设计3个字段的表：id（自增主键）  userId  videoId
     * 这样设计会有问题，id怎么来获取？？
     * 所以改为计数器方式描述总共有多少条数据，id变为一个自增数incr(num)
     * 每次点赞触发后，给num+1；
     * 第一种方式：
     * 以key为 love:num:userId 存入value为 userId，以及以key为 love:num:videoId 存入value为 videoId
     * 如果要统计该用户点赞了哪些视频，就直接循环 num 次，读取所有的 love:num:userId，找出所有当前userId对应的num（数组下标）
     * 再根据下标去获取所有love:num:videoId中所有的videoId....
     *
     * 第二种方式：
     * 以key为 love:num:userId & videoId ，存入value为 userId + videoId 的字符串形式
     * 或 把userId和videoId存到hash中效率稍高，省掉分割字符操作
     * 统计该用户点赞了哪些视频，同样循环 num 次，读取所有love:num:userId & videoId，用 & 分割为array [0]为userId [1]为videoId
     * 找出所有当前 userId 所对应的 num 下标中对应的videoId
     *
     *
     * @return bool
     */
    public function love() {
        $videoId = intval($this->params['videoId']);
        if (empty($videoId)) {
            return $this->writeJson(Status::CODE_BAD_REQUEST, '参数不合法');
        }
        // 这里暂时只做love:num:videoId 关联到 userId 用于查阅当前视频被哪些人点过赞，只适合数据量级小的情况

        $res = Di::getInstance()->get('REDIS')->zincrBy(\Yaconf::get('es_conf.redis.video_love'), 1, $videoId);

    }



    /**
     *  排行计算
     * @param $period 统计周期
     * @param $redisVideoPlayKey 基础key名
     * @throws \Throwable
     */
    private function rankCompute(int $period, $redisVideoPlayKey) {
        $keyArr = [];
        $redis = Redis::defer('redis');
        try{
            if($period === 0){
                //获取总排行（从大到小）
                $resPeriod = $redis->zRevRange($redisVideoPlayKey, 0, -1, true);
                //var_dump($period . $redisVideoPlayKey);
            }else{

                /* 周、月排行实现 根据传递参数决定统计周期 */
                /* 创建周、月排行的 key值array */
                for ($i=0; $i<$period; $i++) {
                    $keyArr[] = $redisVideoPlayKey . date("ymd", strtotime("{$i} days ago"));
                }

                /* 用并集方式 创建 / 更新周、月排行 集合 */
                $redis->zunionstore($redisVideoPlayKey . '_' .$period, $keyArr);

                /* 获取周 / 月排行榜 */
                $resPeriod = $redis->zRevRange($redisVideoPlayKey . '_' .$period, 0, -1,true);
            }
        }catch (\Exception $e) {
            // 补充日志 / 报警....
            //$this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, '服务异常');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),Status::CODE_BAD_REQUEST);
        }

        return $resPeriod;
    }



}