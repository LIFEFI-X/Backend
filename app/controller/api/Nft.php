<?php

namespace app\controller\api;

use app\BaseController;
use app\model\Nfts;
use app\model\Collections;
use app\model\NftStats;
use app\model\Listings;
use app\model\Bids;
use app\model\Orders;
use app\model\Activities;
use app\model\UserStats;
use think\facade\Db;

class Nft extends BaseController
{
    /**
     * NFT details
     * GET /api/nfts/{contractAddress}
     * GET /api/nfts/{contractAddress}/{tokenId}
     */
    public function detail()
    {
        $contractAddress = $this->request->param('contractAddress');
        $tokenId = $this->request->param('tokenId', '');
        
        if (!$contractAddress) {
            \Api::fail('Invalid parameters', 400);
        }
        
        $nft = Nfts::with(['collection', 'stats'])->where('contract_address', $contractAddress)->find();
        
        if (!$nft) {
            \Api::fail('NFT not found', 404);
        }
        
        $collection = $nft->collection ?? null;
        $stats = $nft->stats ?? null;
        
        // Fetch current active listing
        $activeListing = Listings::where('nft_id', $nft->id)
            ->where('status', 'active')
            ->order('id', 'desc')
            ->find();
        
        // Fetch highest bid
        $highestBid = Bids::where('nft_id', $nft->id)
            ->whereIn('status', ['pending', 'approved'])
            ->order('bid_price', 'desc')
            ->find();
        
        $data = [
            'id' => $nft->id,
            'name' => $nft->name,
            'description' => $nft->description,
            'imageUrl' => $nft->image_url,
            'animationUrl' => $nft->animation_url,
            'displayAnimationUrl' => $nft->display_animation_url,
            
            // Collection information
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
            'mintTransactionHash' => $nft->mint_transaction_hash,
            'mintTimestamp' => $nft->mint_timestamp,
            'metadataUri' => $nft->metadata_uri,
            
            // Rarity
            'rarityRank' => $nft->rarity_rank,
            'rarityScore' => (float)$nft->rarity_score,
            
            // Pricing information
            'priceInfo' => [
                'price' => (float)$nft->price,
                'priceCurrency' => $nft->price_currency,
                'usdPrice' => (float)$nft->usd_price,
                'lastSalePrice' => (float)$nft->last_sale_price,
                'listingPrice' => (float)($activeListing->price ?? $nft->listing_price),
                'highestBid' => (float)($highestBid->bid_price ?? $nft->highest_bid),
                'lowestAsk' => (float)$nft->lowest_ask,
                'isListed' => (bool)$nft->is_listed,
                'marketplace' => $nft->marketplace,
                'lastSaleTimestamp' => $nft->last_sale_timestamp,
                'priceChange24h' => 0,
                'tradingVolume24h' => 0
            ],
            
            // Attributes
            'attributes' => $nft->attributes ?? [],
            
            'createdAt' => $nft->created_at,
            'updatedAt' => $nft->updated_at
        ];
        
        // Update view count
        if ($stats) {
            $stats->views = $stats->views + 1;
            $stats->last_viewed_at = date('Y-m-d H:i:s');
            $stats->save();
        }
        
        \Api::success($data, 200, 'Success');
    }
    
    /**
     * Create NFT
     * POST /api/nfts
     */
    public function create()
    {
        $params = $this->getParam([
            ['name', ''],
            ['description', ''],
            ['imageUrl', ''],
            'displayImageUrl' => '',
            'animationUrl' => '',
            'displayAnimationUrl' => '',
            'tokenStandard' => 'ERC721',
            'blockchainNetwork' => 'ethereum',
            ['contractAddress', ''],
            ['metadataUrl', ''],
            ['creatorAddress', ''],
            'collectionId' => 0,
            'price' => 0,
            'listingPrice' => 0,
            'priceCurrency' => '',
            'marketplace' => '',
            'isListed' => 0,
            'rarityRank' => 0,
            'rarityScore' => 0,
            'mintTxHash' => '',
            'tokenId' => ''
        ], [
            'name' => 'require',
            'contractAddress' => 'require',
            'creatorAddress' => 'require'
        ], [
            'name.require' => 'NFT name is required',
            'contractAddress.require' => 'Contract address is required',
            'creatorAddress.require' => 'Creator address is required'
        ]);
        
        Db::startTrans();
        try {
            // Ensure the collection exists
            if ($params['collectionId'] > 0) {
                $collection = Collections::find($params['collectionId']);
                if (!$collection) {
                    \Api::fail('Collection not found', 404);
                }
            }
            
            // Create NFT record
            $nft = Nfts::create([
                'collection_id' => $params['collectionId'] ?: null,
                'owner_address' => $params['creatorAddress'],
                'creator_address' => $params['creatorAddress'],
                'name' => $params['name'],
                'description' => $params['description'],
                'image_url' => $params['imageUrl'],
                'display_image_url' => $params['displayImageUrl'] ?: $params['imageUrl'],
                'animation_url' => $params['animationUrl'],
                'display_animation_url' => $params['displayAnimationUrl'],
                'token_id' => $params['tokenId'],
                'token_id_or_mint' => $params['tokenId'] ?: $params['contractAddress'],
                'contract_address' => $params['contractAddress'],
                'blockchain_network' => $params['blockchainNetwork'],
                'token_standard' => $params['tokenStandard'],
                'metadata_url' => $params['metadataUrl'],
                'metadata_uri' => $params['metadataUrl'],
                'mint_tx_hash' => $params['mintTxHash'],
                'mint_transaction_hash' => $params['mintTxHash'],
                'mint_timestamp' => date('Y-m-d H:i:s'),
                'chain' => strpos($params['blockchainNetwork'], 'solana') !== false ? 'solana' : 'evm',
                'price' => $params['price'],
                'listing_price' => $params['listingPrice'],
                'price_currency' => $params['priceCurrency'],
                'is_listed' => $params['isListed'],
                'marketplace' => $params['marketplace'],
                'rarity_rank' => $params['rarityRank'],
                'rarity_score' => $params['rarityScore'],
                'status' => 'normal'
            ]);
            
            // Initialize statistics
            NftStats::create([
                'nft_id' => $nft->id,
                'views' => 0,
                'favorites' => 0
            ]);
            
            // Update collection statistics
            if ($params['collectionId'] > 0) {
                Collections::where('id', $params['collectionId'])->inc('total_items')->update();
            }
            
            // Record activity entry
            Activities::create([
                'nft_id' => $nft->id,
                'event_type' => 'mint',
                'from_address' => null,
                'to_address' => $params['creatorAddress'],
                'price' => $params['price'],
                'currency' => $params['priceCurrency'],
                'tx_hash' => $params['mintTxHash']
            ]);
            
            Db::commit();
            
            $data = [
                'id' => $nft->id,
                'name' => $nft->name,
                'description' => $nft->description,
                'imageUrl' => $nft->image_url,
                'tokenId' => $nft->token_id,
                'contractAddress' => $nft->contract_address,
                'blockchainNetwork' => $nft->blockchain_network,
                'creatorAddress' => $nft->creator_address,
                'ownerAddress' => $nft->owner_address,
                'mintTimestamp' => $nft->mint_timestamp,
                'mintTxHash' => $nft->mint_tx_hash,
                'metadataUrl' => $nft->metadata_url,
                'createdAt' => $nft->created_at
            ];
            
            Db::commit();
            \Api::success($data, 200, 'NFT created successfully');
            
        } catch (\think\exception\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            \Api::fail($e->getMessage(), 500);
        }
    }
    
    /**
     * Purchase NFT
     * POST /api/nfts/{nftId}/purchase
     */
    public function purchase()
    {
        $nftId = $this->request->param('nftId');
        
        $params = $this->getParam([
            ['buyerAddress', ''],
            ['sellerAddress', ''],
            ['price', 0],
            ['currency', ''],
            'listingId' => 0,
            'bidId' => 0
        ], [
            'buyerAddress' => 'require',
            'sellerAddress' => 'require',
            'price' => 'require|float|gt:0',
            'currency' => 'require'
        ], [
            'buyerAddress.require' => 'Buyer address is required',
            'sellerAddress.require' => 'Seller address is required',
            'price.require' => 'Price is required',
            'price.float' => 'Price must be a number',
            'price.gt' => 'Price must be greater than 0',
            'currency.require' => 'Currency is required'
        ]);
        
        // Retrieve optional proof parameters
        $params['proof'] = $this->request->param('proof', []);
        
        Db::startTrans();
        try {
            $nft = Nfts::find($nftId);
            if (!$nft) {
                \Api::fail('NFT not found', 404);
            }
            
            if ($nft->status != 'normal') {
                \Api::fail('Invalid NFT status', 400);
            }
            
            // Fetch user IDs
            $buyer = \app\model\Users::where('address', $params['buyerAddress'])->find();
            $seller = \app\model\Users::where('address', $params['sellerAddress'])->find();
            
            // Generate order ID
            $orderId = 'ORDER_' . time() . rand(1000, 9999);
            
            // Create order
            $order = Orders::create([
                'order_id' => $orderId,
                'nft_id' => $nftId,
                'listing_id' => $params['listingId'] ?: null,
                'bid_id' => $params['bidId'] ?: null,
                'buyer_user_id' => $buyer->id ?? 0,
                'buyer_address' => $params['buyerAddress'],
                'seller_user_id' => $seller->id ?? 0,
                'seller_address' => $params['sellerAddress'],
                'price' => $params['price'],
                'currency_code' => $params['currency'],
                'chain' => $nft->chain,
                'tx_hash' => $params['proof']['value'] ?? ('TX_' . uniqid()),
                'transfer_method' => $params['proof']['method'] ?? 'direct_buy',
                'status' => 'completed',
                'previous_owner' => $nft->owner_address,
                'new_owner' => $params['buyerAddress'],
                'ownership_updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Transfer NFT ownership
            $nft->owner_address = $params['buyerAddress'];
            $nft->last_sale_price = $params['price'];
            $nft->last_sale_timestamp = date('Y-m-d H:i:s');
            $nft->is_listed = 0;
            $nft->save();
            
            // Close listing
            if ($params['listingId']) {
                Listings::where('id', $params['listingId'])->update(['status' => 'sold']);
            }
            
            // Accept bid
            if ($params['bidId']) {
                Bids::where('id', $params['bidId'])->update(['status' => 'accepted']);
            }
            
            // Record activity entry
            Activities::create([
                'nft_id' => $nftId,
                'event_type' => 'sale',
                'from_address' => $params['sellerAddress'],
                'to_address' => $params['buyerAddress'],
                'price' => $params['price'],
                'currency' => $params['currency'],
                'tx_hash' => $params['proof']['value'] ?? null,
                'marketplace' => $nft->marketplace
            ]);
            
            // Update user statistics
            if ($buyer) {
                UserStats::where('user_id', $buyer->id)->inc('purchases_count')->update();
            }
            if ($seller) {
                UserStats::where('user_id', $seller->id)->inc('sales_count')->update();
                UserStats::where('user_id', $seller->id)->inc('total_volume', $params['price'])->update();
            }
            
            // Update collection statistics
            if ($nft->collection_id) {
                Collections::where('id', $nft->collection_id)->inc('total_volume', $params['price'])->update();
            }
            
            Db::commit();
            
            $data = [
                'orderId' => $order->id,
                'nftId' => $nftId,
                'listingId' => $params['listingId'] ?: null,
                'bidId' => $params['bidId'] ?: null,
                'buyer' => $params['buyerAddress'],
                'buyerId' => $buyer->id ?? 0,
                'seller' => $params['sellerAddress'],
                'sellerId' => $seller->id ?? 0,
                'price' => (float)$params['price'],
                'currency' => $params['currency'],
                'chain' => $nft->chain,
                'txHash' => $order->tx_hash,
                'transferMethod' => $order->transfer_method,
                'status' => $order->status,
                'createdAt' => $order->created_at,
                'nftOwnershipUpdated' => [
                    'previousOwner' => $order->previous_owner,
                    'newOwner' => $order->new_owner,
                    'updatedAt' => $order->ownership_updated_at
                ]
            ];
            
            Db::commit();
            \Api::success($data, 200, 'NFT purchased successfully');
            
        } catch (\think\exception\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            \Api::fail($e->getMessage(), 500);
        }
    }
}


