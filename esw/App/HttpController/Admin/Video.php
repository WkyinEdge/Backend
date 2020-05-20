<?php


namespace App\HttpController\Admin;


use App\Lib\StatusCode;
use App\Model\Api\Category as CateModel;
use App\Model\Api\Video as VideoModel;
use App\Server\Admin\Video as VideoServer;
use EasySwoole\Validate\Validate;

class Video extends AdminBase
{

    /**
     * 当前控制器的所有action 需要校验的 param 以及对应的规则
     * @param string|null $action
     * @return Validate|null
     */
    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'list':
                {
                    $v->addColumn('current_page', 'current_page')->required('不能为空')->integer('必须是数字');
                    $v->addColumn('page_size', 'page_size')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'store':
                {
                    $v->addColumn('title', '视频标题')->required('不能为空');
                    $v->addColumn('category_id', '视频URL')->required('不能为空');
                    $v->addColumn('url', '视频URL')->required('不能为空');
                    $v->addColumn('image', '封面图')->required('不能为空');
                    $v->addColumn('uploader_id', '上传者id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'get_info':
                {
                    $v->addColumn('id', '分类id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'delete':
                {
                    $v->addColumn('id', '分类id')->required('不能为空')->integer('必须是数字');
                    break;
                }
            case 'order':
                {
                    $v->addColumn('ids', 'ids')->required('不能为空');
                    break;
                }
        }
        return $v;
    }


    public function list()
    {
        $res = VideoServer::getVideoList($this->params);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);
    }


    public function category_list()
    {
        $res = CateModel::create()->getInfoByWhere(true, [ 'id' =>[ 1, '!=' ]],['ord','ASC']);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);
    }


    public function store()
    {
        $res = VideoServer::saveVideo($this->params);
        if ( empty($res) )
            return $this->writeJson(StatusCode::WARNING, '失败');
        else
            return $this->writeJson(StatusCode::SUCCESS, 'ok', $res);
    }

    public function get_info()
    {
        $res = VideoModel::create()->getById($this->params['id']);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);

    }

    public function delete()
    {
        $res = VideoModel::create()->delete($this->params['id']);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);

    }



}