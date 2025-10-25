<?php

namespace app\controller\api;

use app\BaseController;
use app\model\Nfts;
use app\model\Collections;

class Marketplace extends BaseController
{
    /**
     * Marketplace NFT list
     * GET /api/marketplace/nfts
     */
    public function nfts()
    {
        $params = $this->getParam([
            'pageNum' => 1,
            'pageSize' => 20,
            'status' => 0,           // 1-Buy Now 2-Auction 3-New
            'marketplace' => '',
            'minPrice' => 0,
            'maxPrice' => 0,
            'currency' => '',
            'category' => '',
            'collectionId' => 0,
            'sort' => '',            // price_asc/price_desc/latest/popular
            'q' => ''                // Search keyword
        ]);
        
        $pageNum = max(1, intval($params['pageNum']));
        $pageSize = min(100, max(1, intval($params['pageSize'])));
        
        // Build query
        $query = Nfts::with(['collection']);
        
        // Search filters
        if (!empty($params['q'])) {
            $query->where('name', 'like', '%' . $params['q'] . '%');
        }
        
        // Status filter
        if ($params['status'] == 1) {
            $query->where('is_listed', 1);
        } elseif ($params['status'] == 3) {
            $query->order('created_at', 'desc');
        }
        
        // Marketplace filter
        if (!empty($params['marketplace'])) {
            $query->where('marketplace', $params['marketplace']);
        }
        
        // Price range filter
        if ($params['minPrice'] > 0) {
            $query->where('price', '>=', $params['minPrice']);
        }
        if ($params['maxPrice'] > 0) {
            $query->where('price', '<=', $params['maxPrice']);
        }
        
        // Currency filter
        if (!empty($params['currency'])) {
            $query->where('price_currency', $params['currency']);
        }
        
        // Category filter
        if (!empty($params['category'])) {
            $query->where('category', $params['category']);
        }
        
        // Collection filter
        if ($params['collectionId'] > 0) {
            $query->where('collection_id', $params['collectionId']);
        }
        
        // Sorting
        switch ($params['sort']) {
            case 'price_asc':
                $query->order('price', 'asc');
                break;
            case 'price_desc':
                $query->order('price', 'desc');
                break;
            case 'latest':
                $query->order('created_at', 'desc');
                break;
            case 'popular':
                $query->order('id', 'desc'); // Adjust as needed
                break;
            default:
                $query->order('id', 'desc');
        }
        
        // Paginated query
        $list = $query->paginate([
            'list_rows' => $pageSize,
            'page' => $pageNum
        ]);
        
        // Assemble response data
        $records = [];
        foreach ($list as $nft) {
            $collection = $nft->collection ?? null;
            
            $records[] = [
                'id' => $nft->id,
                'name' => $nft->name,
                'description' => $nft->description,
                'imageUrl' => $nft->image_url,
                'animationUrl' => $nft->animation_url,
                'displayImageUrl' => $nft->display_image_url,
                'displayAnimationUrl' => $nft->display_animation_url,
                'externalUrl' => $nft->external_url,
                
                // Collection information
                'collectionId' => $nft->collection_id,
                'collectionName' => $collection->name ?? '',
                'collection' => $collection ? [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'imageUrl' => $collection->image_url,
                    'category' => $collection->category,
                    'isVerified' => (bool)$collection->is_verified,
                    'floorPrice' => (float)$collection->floor_price,
                    'totalVolume' => (float)$collection->total_volume,
                    'totalItems' => $collection->total_items,
                    'royaltyPercentage' => (float)$collection->royalty_percentage
                ] : null,
                
                // On-chain information
                'tokenId' => $nft->token_id,
                'creatorAddress' => $nft->creator_address,
                'ownerAddress' => $nft->owner_address,
                'contractAddress' => $nft->contract_address,
                'blockchainNetwork' => $nft->blockchain_network,
                'mintTimestamp' => $nft->mint_timestamp,
                'mintTransactionHash' => $nft->mint_transaction_hash,
                
                // Rarity
                'rarityRank' => $nft->rarity_rank,
                'rarityScore' => (float)$nft->rarity_score,
                
                // Pricing information
                'price' => (float)$nft->price,
                'priceCurrency' => $nft->price_currency,
                'usdPrice' => (float)$nft->usd_price,
                'lastSalePrice' => (float)$nft->last_sale_price,
                'listingPrice' => (float)$nft->listing_price,
                'highestBid' => (float)$nft->highest_bid,
                'lowestAsk' => (float)$nft->lowest_ask,
                'isListed' => $nft->is_listed,
                'marketplace' => $nft->marketplace,
                'category' => $nft->category,
                'lastSaleTimestamp' => $nft->last_sale_timestamp,
                'createdAt' => $nft->created_at
            ];
        }
        
        $data = [
            'size' => count($records),
            'records' => $records,
            'total' => $list->total(),
            'current' => $pageNum,
            'pages' => ceil($list->total() / $pageSize)
        ];
        
        \Api::success($data, 200, 'Success');
    }
}


