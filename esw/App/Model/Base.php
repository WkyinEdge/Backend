<?php


namespace App\Model;

use App\Server\Admin\User;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;

class Base extends AbstractModel
{

    public function __construct()
    {
        //parent::__construct($data);
        if (empty($this->tableName)) {
            throw new \Exception('table error');
        }
    }

    /**
     * 演示 拦截：增 删 改
     * @throws \Exception
     */
    public function check()
    {
        $uid = User::check();

        if ($uid != intval(\Yaconf::get('es_conf.admin.super_admin_id'))){
            throw new \Exception('DEMO演示，禁止危险操作！');
        }
    }

    /**
     * 通用添加
     * @param array $data
     * @return bool|int
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function add(array $data)
    {
        $this->check();
        try {
            if (empty($data))
                return false;
            $id = $this->data($data)->save();
        } catch (\Exception $e) {
            var_dump('Model\Base-'.'add');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $id;
    }

    /**
     * 通用修改
     * @param $id
     * @param $data
     * @return bool
     * @throws \Throwable
     */
    public function updateById($id, $data)
    {
        $this->check();
        try {
            $row = $this->get($id);

            $suc = $row->update($data);
            if ($suc === false) {
                // 更新失败逻辑
                var_dump('Model\Base-'.'updateById');
                throw new \Exception($row->lastQueryResult()->getLastError());
            }elseif ($suc !== true){
                //var_dump($suc);
                // 返回受影响行数
                $res = $row->lastQueryResult()->getAffectedRows();
            }elseif ($suc === true){
                // 正常执行了,但没有受影响的行
                $res = true;
            }


        } catch (\Exception $e) {
            var_dump('Model\Base-'.'updateById');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        // 如果影响行数为 0 或不存在，更新失败
        return !empty($res) ? true : false;
    }

    /**
     * 通用删除
     * @param $id
     * @param bool $type 是否真删除
     * @return bool|int
     * @throws \Throwable
     */
    public function delete($id, $type = true) {
        $this->check();
        try {
            if (!$type){
                // 软删除
                $res = $this->updateById($id, [
                    'status' => \Yaconf::get('es_conf.status.delete')
                ]);
            }else{
                // 真删除
                $res = $this->destroy($id);
            }

        }catch (\Exception $e) {
            var_dump('Model\Base-'.'delete');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $res;
    }

    /**
     * 通过主键Id 快查
     * @param $id
     * @param array $fields 需要查询字段
     * @param array $hidden 需要隐藏字段
     * @return Base|array|bool|null
     * @throws \Throwable
     */
    public function getById($id, $fields = [], $hidden = [])
    {
        $id = intval($id);
        if (empty($id))
            return [];
        try {
            $res = $this->get($id);
            if ( !empty($fields) ){
                $res->field($fields);
            }
            if ( !empty($hidden) ){
                $res->hidden($hidden);
            }
            $res = $res->toArray();
        } catch (\Exception $e) {
            var_dump('Model\Base-'.'getById');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        return $res;
    }

    /**
     * 通用查询
     * @param $where
     * @param $order = ['id','ASC']
     * @param bool $isAll 是否查所有
     * @param array $fields 指定查询字段
     * @return array
     * @throws \Throwable
     */
    public function getInfoByWhere($isAll = false, $where = [], $order = [], $fields = [])
    {
        try {
            $status = ['status' => \Yaconf::get('es_conf.status.normal')];
            $where = array_merge($status, $where);

            $obj = $this->where($where);

            // Orm自带封装的 order 函数就是个bug，所以暂时只能用MySQL闭包处理，且不好抽离封装
            $res = $isAll ? $obj->all(function (QueryBuilder $queryBuilder) use ($order, $fields) {

                if (is_array($order) && !empty($order) && isset($order[0]))
                    $queryBuilder->orderBy($order[0], $order[1] ?? 'ASC');

                if (is_array($fields) && !empty($fields) && isset($fields[0]))
                    $queryBuilder->fields($fields);

            }) : $obj->get(function (QueryBuilder $queryBuilder) use ($order, $fields) {

                if (is_array($order) && !empty($order) && isset($order[0]))
                    $queryBuilder->orderBy($order[0], $order[1] ?? 'ASC');

                if (is_array($fields) && !empty($fields) && isset($fields[0]))
                    $queryBuilder->fields($fields);
            });

            //$res = $obj->lastQueryResult();  // 最后执行结果 可简化其他复杂操作：看文档
            //var_dump($obj->lastQuery()->getLastQuery()); // 打印sql语句

        } catch (\Exception $e) {
            // 写日志、报警...
            var_dump('Model\Base-'.'getInfoByWhere');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }

        return is_array($res) ? $res : ( !empty($res) ? $res->toArray() : [] );
    }


    /**
     * 通用的分页方法
     * @param int $current_page 当前页码
     * @param int $page_size 每页数量
     * @param array $where where条件
     * @param array $order
     * @param array $fields 指定查询字段
     * @return array
     * @throws \Throwable
     */
    public function getPage($current_page = 1, $page_size = 10, $where = [], $order = [], $fields=[])
    {
        try {
            $status = ['status' => \Yaconf::get('es_conf.status.normal')];
            $where = array_merge($status, $where);

            $table = $this->where($where)
                //->order($order)
                ->page($current_page, $page_size);

            // Orm自带封装的 order 函数就是个bug，所以暂时只能用MySQL闭包处理，且不好抽离封装
            $lists = $table->all(function (QueryBuilder $queryBuilder) use ($order, $fields) {

                if (is_array($order) && !empty($order) && isset($order[0]))
                    $queryBuilder->orderBy($order[0], $order[1] ?? 'ASC');

                if (is_array($fields) && !empty($fields) && isset($fields[0]))
                    $queryBuilder->fields($fields);
            });

            $res = $table->lastQueryResult();   // 最后执行结果 可简化其他复杂操作：看文档
            //var_dump($table->lastQuery()->getLastQuery()); // 打印sql语句

            /* 获取总条数类似于count(*) */
            $total = $res->getTotalCount();
            /* 求总页数 */
            $totalPages = ceil($total / $page_size);

        } catch (\Exception $e) {
            var_dump('Model\Base-'.'getPage');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }

        $data = [
            'pageInfo' => [
                'current_page' => $current_page,            // 当前页数
                'offset' => ($current_page - 1) * $page_size, // 当前页起始条数
                'page_size' => $page_size,                  // 每页多少条
                'totalPages' => $totalPages,                // 总页数
                'total' => $total,                          // 总条数
            ],
            'data' => $lists                           // 结果
        ];
        return $data;
    }


    /**
     * 废弃 ：添加修改一体，区别在于有无主键，正常返回值为一个 装有 N 个Model的数组，需要调用者处理
     * @param array $data
     * @return array|bool
     * @throws \Throwable
     */
    public function addOrUpdate(array $data)
    {
        if (empty($data))
            return false;
        try {

            $res = $this->saveAll($data);
           // var_dump($res);
            exit;
            //$res1 = $this->lastQueryResult()->getAffectedRows();


        } catch (\Exception $e) {
            var_dump('Model\Base-'.'addOrUpdate');
            throw new \Exception(__METHOD__ . ' -- ' . $e->getMessage());
        }
        if (empty($res[0]))
            return false;

        return $res;
    }


}