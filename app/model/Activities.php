<?php

namespace app\model;

use think\Model;

class Activities extends Model
{
    protected $name = 'nft_activities';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;
}

