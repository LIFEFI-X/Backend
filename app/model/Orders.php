<?php

namespace app\model;

use think\Model;

class Orders extends Model
{
    protected $name = 'nft_orders';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // Associate NFT relation
    public function nft()
    {
        return $this->belongsTo(Nfts::class, 'nft_id');
    }
}


