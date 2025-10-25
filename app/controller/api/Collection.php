<?php

namespace app\controller\api;

use app\BaseController;
use app\model\Collections;
use think\facade\Db;

class Collection extends BaseController
{
    /**
     * Create collection
     * POST /api/collections
     */
    public function create()
    {
        $params = $this->getParam([
            ['name', ''],
            ['description', ''],
            ['shortUrl', ''],
            ['imageUrl', ''],
            ['bannerImageUrl', ''],
            ['category', ''],
            ['projectUrl', ''],
            ['mintPrice', 0],
            ['priceUnits', '1'],
            ['royaltyFee', 0],
            ['maxSupply', 0],
            ['mintLimit', 0],
            ['chain', 'evm'],
            ['contractOrMint', ''],
            'createTxHash' => '',
            ['creatorAddress', '']
        ], [
            'name' => 'require',
            'shortUrl' => 'require'
        ], [
            'name.require' => 'Name is required',
            'shortUrl.require' => 'Short URL is required'
        ]);
        
        Db::startTrans();
        try {
            // Ensure shortUrl is unique
            $exists = Collections::where('short_url', $params['shortUrl'])->find();
            if ($exists) {
                \Api::fail('Short link already exists', 400);
            }
            
            // If a contract address is provided, ensure it is unique
            if (!empty($params['contractOrMint'])) {
                $existsContract = Collections::where([
                    'chain' => $params['chain'],
                    'contract_or_mint' => $params['contractOrMint']
                ])->find();
                if ($existsContract) {
                    \Api::fail('Contract address already exists', 400);
                }
            }
            
            // Resolve creator info; use the current logged-in user when missing
            $creatorAddress = !empty($params['creatorAddress']) 
                ? $params['creatorAddress'] 
                : ($this->request->userAddress ?? '');
            
            $creator = null;
            if (!empty($creatorAddress)) {
                $creator = \app\model\Users::where('address', $creatorAddress)->find();
            }
            
            // Create collection record
            $collection = Collections::create([
                'creator_user_id' => $creator->id ?? null,
                'creator_address' => !empty($creatorAddress) ? $creatorAddress : null,
                'name' => $params['name'],
                'description' => $params['description'],
                'short_url' => $params['shortUrl'],
                'image_url' => $params['imageUrl'],
                'banner_image_url' => $params['bannerImageUrl'],
                'category' => $params['category'],
                'project_url' => $params['projectUrl'],
                'chain' => $params['chain'],
                'contract_or_mint' => !empty($params['contractOrMint']) ? $params['contractOrMint'] : null,
                'create_tx_hash' => $params['createTxHash'],
                'mint_price' => $params['mintPrice'],
                'price_units' => $params['priceUnits'],
                'royalty_fee' => $params['royaltyFee'],
                'royalty_percentage' => $params['royaltyFee'],
                'max_supply' => $params['maxSupply'],
                'mint_limit' => $params['mintLimit'],
                'floor_price' => $params['mintPrice'], // Initial floor price equals the mint price
                'total_volume' => 0,
                'total_items' => 0,
                'is_verified' => 0,
                'status' => 'active'
            ]);
            
            Db::commit();
            
            $data = [
                'id' => $collection->id,
                'name' => $collection->name,
                'description' => $collection->description,
                'shortUrl' => $collection->short_url,
                'imageUrl' => $collection->image_url,
                'bannerImageUrl' => $collection->banner_image_url,
                'category' => $collection->category,
                'projectUrl' => $collection->project_url,
                'mintPrice' => (float)$collection->mint_price,
                'priceUnits' => $collection->price_units,
                'royaltyFee' => (float)$collection->royalty_fee,
                'maxSupply' => $collection->max_supply,
                'mintLimit' => $collection->mint_limit,
                'chain' => $collection->chain,
                'contractOrMint' => $collection->contract_or_mint,
                'creator' => $creatorAddress,
                'creatorAddress' => $collection->creator_address,
                'createTxHash' => $collection->create_tx_hash,
                'isVerified' => (bool)$collection->is_verified,
                'floorPrice' => (float)$collection->floor_price,
                'totalVolume' => (float)$collection->total_volume,
                'totalItems' => $collection->total_items,
                'createdAt' => $collection->created_at,
                'updatedAt' => $collection->updated_at,
                'success' => true
            ];
            
            \Api::success($data, 200, 'Collection created successfully');
            
        } catch (\think\exception\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            \Api::fail($e->getMessage(), 500);
        }
    }
}


