<?php


namespace App\HttpController\Api;

//use App\Lib\Redis\Redis;
use App\Lib\Cache\ApiCache;
use App\Lib\Utils;
use App\Model\Api\Video;
use EasySwoole\Component\Di;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Message\Status;
use \EasySwoole\EasySwoole\Logger;
use EasySwoole\Template\Render;


class Index extends ApiBase
{
    // 测试模板引擎
    public function index()
    {
        // 测试模板引擎
        $user_list = ['1', '2', '3', '4', '5'];
        $this->assign('user_list', $user_list);
        $this->fetch('index');
    }

    /**
     * 主页视频列表数据 — 第一套方案： 原始读取MySQL
     * @return bool
     * @throws \Throwable
     */
    public function lists0() {
        //var_dump($this->params);
        $condition = [];
        if(!empty($this->params['cat_id'])) {
            $condition['cat_id'] = intval($this->params['cat_id']);
        }
        //var_dump($condition);
        //var_dump($limit);
        try{
            $model = new Video();
            $data = $model->getPage($this->params['page'],$this->params['size'],$condition);
        } catch (\Exception $e){
            // 异常信息写入日志
            //Logger::getInstance()->log($e->getMessage());
            //return $this->writeJson(Status::CODE_BAD_REQUEST,'服务异常');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),Status::CODE_BAD_REQUEST);
        }
        if(!empty($data['lists'])){
            foreach ($data['lists'] as &$list){
                $list['create_time'] = date('Ymd H:i:s',$list['create_time']);
                /* 用秒数 转为：H:M:S的格式 gmstrftime("%H:%M:%S") */
            }
        }

        return $this->writeJson(Status::CODE_OK,'OK', $data);
    }

    /**
     * 第二套方案： 直接读取 静态化数据 （本地文件json数据、fastCache、Redis，可在配置文件中设置缓存类型）
     * @return bool
     * @throws \Throwable
     */
    public function lists() {

        $catId = $this->params['category_id'];

        try{
            $videoData = ApiCache::getCache($catId);

        }catch (\Exception $e){
            //return $this->writeJson(400,'请求失败');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage(),Status::CODE_BAD_REQUEST);
        }
        $count = count($videoData);

        return $this->writeJson(Status::CODE_OK,'OK', $this->getPagingDatas($count, $videoData));
    }




    public function video()
    {
        $data = [
            'id' => 1,
            'name' => 'N牛逼',
            'params'=>$this->request()->getRequestParam()
        ];
        return $this->writeJson(200,'ok',$data);
    }

    public function getVideo(){
        $model = new Video();
        $data = $model->all();
        return $this->writeJson(200,'查询成功',$data);
    }

    public function getRedis() {

        $result = Di::getInstance()->get('REDIS')->get('wky123');
        return $this->writeJson(200,'ok',$result);
    }

    public function yaconf() {
        $result = \Yaconf::get('redis');
        return $this->writeJson(200,'ok',$result);
    }

    /**生产者方法
     * @throws \Throwable
     */
    public function rub() {
        $param = $this->request()->getRequestParam('f');
        Di::getInstance()->get('REDIS')->rPush('wky_list_test', $param);
        //return $this->writeJson(200,'ok',$result);

    }

}