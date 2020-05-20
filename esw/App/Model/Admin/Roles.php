<?php


namespace App\Model\Admin;

use App\Model\Base;
use EasySwoole\Mysqli\QueryBuilder;


class Roles extends Base
{
    protected $tableName = 'admin_roles';

    protected $autoTimeStamp = true;
    //protected $createTime = 'create_time';
    //protected $updateTime = 'update_time';

    public function user($current_page = 1, $page_size = 1)
    {
        return $this->hasMany(User::class, function(QueryBuilder $query) use ($current_page, $page_size){
            $query->limit(($current_page - 1) * $page_size, $page_size);
            //var_dump($page_size );
            return $query;
        }, 'id','role_id');
    }

    public function rolesWithPermissions()
    {
        return $this->hasMany(RolesWithPermissions::class, null, 'id','role_id');
    }



}