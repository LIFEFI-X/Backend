<?php

namespace app\controller\api;

use app\BaseController;
use app\model\KnowledgeTransfers;
use think\facade\Db;
use think\facade\Log;

/**
 * Extension API controller
 * Class Extension
 * @package app\controller\api
 */
class Extension extends BaseController
{
    /**
     * Create knowledge-base transfer
     * POST /api/extension/transfer/create
     * 
     * @return void
     */
    public function createTransfer()
    {
        // 1. Capture and validate parameters
        $params = $this->getParam([
            ['knowledgeBases', []],
            ['preferences', []]
        ], [
            'knowledgeBases' => 'require|array'
        ], [
            'knowledgeBases.require' => 'Knowledge bases are required',
            'knowledgeBases.array' => 'Knowledge bases must be an array'
        ]);
        
        // 2. Validate knowledge base count
        $kbCount = count($params['knowledgeBases']);
        if ($kbCount === 0) {
            \Api::fail('At least one knowledge base is required', 400);
        }
        if ($kbCount > 10) {
            \Api::fail('Maximum 10 knowledge bases allowed', 400);
        }
        
        // 3. Ensure required fields exist for each knowledge base
        foreach ($params['knowledgeBases'] as $index => $kb) {
            if (!isset($kb['id']) || !isset($kb['title']) || !isset($kb['content'])) {
                \Api::fail("Knowledge base at index {$index} is missing required fields (id, title, content)", 400);
            }
        }
        
        // 4. Calculate total data size
        $totalSize = strlen(json_encode($params['knowledgeBases']));
        if ($totalSize > 10 * 1024 * 1024) { // 10MB
            \Api::fail('Data size exceeds 10MB limit', 400);
        }
        
        Db::startTrans();
        try {
            // 5. Generate a unique transfer ID
            $transferId = 'KBT_' . time() . '_' . bin2hex(random_bytes(8));
            
            // 6. Fetch user info when logged in
            $userId = $this->request->userId ?? null;
            $userAddress = $this->request->userAddress ?? null;
            
            // 7. Build transfer payload
            $transferData = [
                'transfer_type' => $kbCount > 1 ? 'multiple' : 'single',
                'knowledge_bases' => $params['knowledgeBases'],
                'preferences' => $params['preferences'] ?? [],
                'timestamp' => time(),
                'source' => 'write-box-extension',
                'version' => '1.0'
            ];
            
            // 8. Create transfer record
            $transfer = KnowledgeTransfers::create([
                'transfer_id' => $transferId,
                'user_id' => $userId,
                'user_address' => $userAddress,
                'transfer_data' => $transferData,
                'knowledge_count' => $kbCount,
                'total_size' => $totalSize,
                'source' => 'extension',
                'version' => '1.0',
                'language' => $params['preferences']['language'] ?? 'en',
                'status' => 'pending'
            ]);
            
            Db::commit();
            
            // 9. Build response payload
            $frontendUrl = config('app.frontend_url') ?? 'https://www.lifefi.io';
            
            $data = [
                'transferId' => $transferId,
                'knowledgeCount' => $kbCount,
                'totalSize' => $totalSize,
                'url' => $frontendUrl . '/create-nft?transfer=' . $transferId
            ];
            
            // Write log entry
            Log::info('Knowledge transfer created', [
                'transfer_id' => $transferId,
                'knowledge_count' => $kbCount,
                'total_size' => $totalSize
            ]);
            
            \Api::success($data, 200, 'Transfer created successfully');
            
        } catch (\think\exception\HttpResponseException $e) {
            Db::rollback();
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('Failed to create transfer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            \Api::fail('Failed to create transfer: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Retrieve transfer data
     * GET /api/extension/transfer/:transferId
     * 
     * @return void
     */
    public function getTransfer()
    {
        // 1. Read transfer ID
        $transferId = $this->request->param('transferId');
        
        if (!$transferId) {
            \Api::fail('Transfer ID is required', 400);
        }
        
        // 2. Query stored transfer
        $transfer = KnowledgeTransfers::where('transfer_id', $transferId)->find();
        
        if (!$transfer) {
            Log::warning('Transfer not found', ['transfer_id' => $transferId]);
            \Api::fail('Transfer not found', 404);
        }
        
        Db::startTrans();
        try {
            // 3. Mark status as consumed
            $transfer->status = 'consumed';
            $transfer->consumed_at = date('Y-m-d H:i:s');
            $transfer->save();
            
            Db::commit();
            
            // 4. Return response data
            $data = [
                'transferId' => $transfer->transfer_id,
                'data' => $transfer->transfer_data,
                'knowledgeCount' => $transfer->knowledge_count,
                'totalSize' => $transfer->total_size,
                'language' => $transfer->language,
                'createdAt' => $transfer->created_at
            ];
            
            // Write log entry
            Log::info('Transfer data retrieved', [
                'transfer_id' => $transferId,
                'knowledge_count' => $transfer->knowledge_count
            ]);
            
            \Api::success($data, 200, 'Transfer data retrieved successfully');
            
        } catch (\think\exception\HttpResponseException $e) {
            Db::rollback();
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('Failed to retrieve transfer: ' . $e->getMessage(), [
                'transfer_id' => $transferId,
                'trace' => $e->getTraceAsString()
            ]);
            \Api::fail('Failed to retrieve transfer: ' . $e->getMessage(), 500);
        }
    }
}


