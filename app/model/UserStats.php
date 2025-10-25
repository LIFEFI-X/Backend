<?php

namespace app\model;

use think\Model;

class UserStats extends Model
{
    protected $name = 'nft_user_stats';
    protected $pk = 'user_id';
    
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = false;
    protected $updateTime = 'updated_at';
}

