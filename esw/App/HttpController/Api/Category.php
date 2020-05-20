<?php


namespace App\HttpController\Api;


use App\Lib\StatusCode;
use App\Model\Api\Category as CateModel;
use App\Server\Base;

class Category extends ApiBase
{

    public function list()
    {
        $res = CateModel::create()->getInfoByWhere(true,['id'=>[1, '!=']],['ord','AES']);

        return $this->writeJson(StatusCode::WARNING, 'ok',$res);
        /*$categorys = \Yaconf::get('es_category.cats');
        return $this->writeJson(200,'OK',$categorys);*/
    }

    public function store()
    {
        $res = CateModel::create()->getInfoByWhere(true,[],['ord','AES']);

        return $this->writeJson(StatusCode::WARNING, 'ok',$res);

    }

    public function get_info()
    {
        $res = CateModel::create()->getById($this->params['id']);

        return $this->writeJson(StatusCode::WARNING, 'ok',$res);

    }

    public function delete()
    {
        $res = CateModel::create()->delete($this->params['id']);

        return $this->writeJson(StatusCode::WARNING, 'ok',$res);

    }

    public function order()
    {
        $ids = $this->params['ids'];
        if ( is_string($ids) ) {
            $ids = explode(',', $this->params['ids']);
        }
        if (empty($ids)) {
            return $this->writeJson(StatusCode::WARNING, '请求参数错误');
        }
        $res = Base::dragSort($ids, 'Category');
        if (empty($res)) {
            return $this->writeJson(StatusCode::WARNING, '操作失败');
        }
        return $this->writeJson(Status::CODE_OK, 'ok');

    }

}