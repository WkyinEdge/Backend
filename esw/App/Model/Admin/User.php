<?php


namespace App\Model\Admin;

use App\Model\Base;


class User extends Base
{
    protected $tableName = 'admin_user';

    protected $autoTimeStamp = true;
    //protected $createTime = 'create_time';
    //protected $updateTime = 'update_time';

    public function roles()
    {
        return $this->hasOne(Roles::class, null, 'role_id','id');
    }

}