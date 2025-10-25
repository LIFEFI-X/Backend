<?php

namespace app\model;

use think\Model;

/**
 * Knowledge transfer model
 * Class KnowledgeTransfers
 * @package app\model
 */
class KnowledgeTransfers extends Model
{
    // Table name with prefix
    protected $name = 'nft_knowledge_transfers';
    
    // Primary key
    protected $pk = 'id';
    
    // Auto-write timestamps
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // Auto-convert JSON fields
    protected $json = ['transfer_data'];
    
    // Type casting
    protected $type = [
        'knowledge_count' => 'integer',
        'total_size' => 'integer',
        'user_id' => 'integer',
        'consumed_at' => 'datetime'
    ];
    
    // Writable fields
    protected $field = [
        'id',
        'transfer_id',
        'user_id',
        'user_address',
        'transfer_data',
        'knowledge_count',
        'total_size',
        'source',
        'version',
        'language',
        'status',
        'consumed_at',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Associated user relation
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}


