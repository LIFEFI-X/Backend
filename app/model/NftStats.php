<?php

namespace app\model;

use think\Model;

class NftStats extends Model
{
    protected $name = 'nft_nft_stats';
    protected $pk = 'nft_id';
    
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = false;
    protected $updateTime = 'updated_at';
}

