<?php


namespace App\Lib\SwooleTable;

use EasySwoole\Component\Singleton;
use Swoole\Table;

class FdManager
{
    use Singleton;

    private $fdUserId;//fd=>userId
    private $userIdFd;//userId=>fd

    function __construct(int $size = 1024*256)
    {
        $this->fdUserId = new Table($size);
        $this->fdUserId->column('userId',Table::TYPE_STRING,25);
        $this->fdUserId->create();
        $this->userIdFd = new Table($size);
        $this->userIdFd->column('fd',Table::TYPE_INT,10);
        $this->userIdFd->create();
    }

    function bind(int $fd,int $userId)
    {
        $this->fdUserId->set($fd,['userId'=>$userId]);
        $this->userIdFd->set($userId,['fd'=>$fd]);
    }

    function delete(int $fd)
    {
        $userId = $this->fdUserId($fd);
        if($userId){
            $this->userIdFd->del($userId);
        }
        $this->fdUserId->del($fd);
    }

    function fdUserId(int $fd):?string
    {
        $ret = $this->fdUserId->get($fd);
        if($ret){
            return $ret['userId'];
        }else{
            return null;
        }
    }

    function userIdFd(int $userId):?int
    {
        $ret = $this->userIdFd->get($userId);
        if($ret){
            return $ret['fd'];
        }else{
            return null;
        }
    }
}