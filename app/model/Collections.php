<?php

namespace app\model;

use think\Model;

class Collections extends Model
{
    protected $name = 'nft_collections';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // Associate NFTs relation
    public function nfts()
    {
        return $this->hasMany(Nfts::class, 'collection_id');
    }
}


