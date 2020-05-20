<?php


namespace App\HttpController\Admin;


use App\Lib\StatusCode;
use App\Model\Api\Category as CateModel;
use App\Server\Base;
use EasySwoole\Validate\Validate;

class Category extends AdminBase
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
            case 'store':
                {
                    $v->addColumn('name', '分类名')->required('不能为空');
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
        $res = CateModel::create()->getInfoByWhere(true, [], ['ord','ASC']);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);
        /*$categorys = \Yaconf::get('es_category.cats');
        return $this->writeJson(200,'OK',$categorys);*/
    }

    public function store()
    {
        if ( !empty($this->params['id']) && isset($this->params['id']) )
            CateModel::create()
                ->updateById($this->params['id'], [
                'name' => $this->params['name']
            ] );
        else
            CateModel::create()->add( ['name' => $this->params['name']] );

        return $this->writeJson(StatusCode::SUCCESS, 'ok');

    }

    public function get_info()
    {
        $res = CateModel::create()->getById($this->params['id']);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);

    }

    public function delete()
    {
        $res = CateModel::create()->delete($this->params['id']);

        return $this->writeJson(StatusCode::SUCCESS, 'ok',$res);

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
        return $this->writeJson(StatusCode::SUCCESS, 'ok');

    }

}