<?php

namespace app\model;

use think\Model;

class Wallets extends Model
{
    protected $name = 'nft_wallets';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // Associate user relation
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}


