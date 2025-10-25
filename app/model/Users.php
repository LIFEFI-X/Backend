<?php

namespace app\model;

use think\Model;

class Users extends Model
{
    protected $name = 'nft_users';
    protected $pk = 'id';
    
    // Automatic timestamps
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // JSON fields
    protected $json = ['social_links'];
    
    // Associated wallets relation
    public function wallets()
    {
        return $this->hasMany(Wallets::class, 'user_id');
    }
    
    // Associated stats relation
    public function stats()
    {
        return $this->hasOne(UserStats::class, 'user_id');
    }
}


