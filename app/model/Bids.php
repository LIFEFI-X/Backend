<?php

namespace app\model;

use think\Model;

class Bids extends Model
{
    protected $name = 'nft_bids';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = false;
    protected $updateTime = 'updated_at';
    
    // Associate NFT relation
    public function nft()
    {
        return $this->belongsTo(Nfts::class, 'nft_id');
    }
}


