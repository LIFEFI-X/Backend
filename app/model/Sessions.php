<?php

namespace app\model;

use think\Model;

class Sessions extends Model
{
    protected $name = 'nft_sessions';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;
    
    // Associate user relation
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}


