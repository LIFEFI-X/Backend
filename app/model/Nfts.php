<?php

namespace app\model;

use think\Model;

class Nfts extends Model
{
    protected $name = 'nft_nfts';
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // JSON fields
    protected $json = ['attributes', 'price_info'];
    
    // Associated collection relation
    public function collection()
    {
        return $this->belongsTo(Collections::class, 'collection_id');
    }
    
    // Associated stats relation
    public function stats()
    {
        return $this->hasOne(NftStats::class, 'nft_id');
    }
}


