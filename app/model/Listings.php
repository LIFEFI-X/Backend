<?php

namespace app\model;

use think\Model;

class Listings extends Model
{
    protected $name = 'nft_listings';
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


