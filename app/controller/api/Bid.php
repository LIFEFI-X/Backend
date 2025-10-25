<?php

namespace app\controller\api;

use app\BaseController;
use app\model\Bids;
use app\model\Nfts;
use app\model\Activities;
use think\facade\Db;

class Bid extends BaseController
{
    /**
     * NFT bidding
     * POST /api/bids/create
     */
    public function create()
    {
        // New endpoint: POST /api/bids/create
        // nftId comes from the request body
        $params = $this->getParam([
            'nftId' => 0,
            'bidderAddress' => '',
            'bidPrice' => 0,
            'currency' => '',
            'expireAt' => 0
        ], [
            'nftId' => 'require|integer',
            'bidderAddress' => 'require',
            'bidPrice' => 'require|float|gt:0',
            'currency' => 'require',
            'expireAt' => 'require|integer'
        ], [
            'nftId.require' => 'NFT ID is required',
            'nftId.integer' => 'NFT ID must be an integer',
            'bidderAddress.require' => 'Bidder address is required',
            'bidPrice.require' => 'Bid price is required',
            'bidPrice.float' => 'Bid price must be a number',
            'bidPrice.gt' => 'Bid price must be greater than 0',
            'currency.require' => 'Currency is required',
            'expireAt.require' => 'Expire time is required',
            'expireAt.integer' => 'Expire time must be an integer'
        ]);
        
        // Retrieve optional proof parameters
        $params['proof'] = $this->request->param('proof', []);
        
        $nftId = $params['nftId'];
        
        Db::startTrans();
        try {
            // Ensure the NFT exists
            $nft = Nfts::find($nftId);
            if (!$nft) {
                \Api::fail('NFT not found', 404);
            }
            
            if ($nft->status != 'normal') {
                \Api::fail('Invalid NFT status', 400);
            }
            
            // Prevent bidding on your own NFT
            if ($nft->owner_address == $params['bidderAddress']) {
                \Api::fail('Cannot bid on your own NFT', 400);
            }
            
            // Fetch bidder information
            $bidder = \app\model\Users::where('address', $params['bidderAddress'])->find();
            
            // Generate bid_id
            $bidId = time() * 1000 + rand(100, 999);
            
            // Create bid entry
            $bid = Bids::create([
                'bid_id' => $bidId,
                'nft_id' => $nftId,
                'bidder_id' => $bidder->id ?? null,
                'bidder_user_id' => $bidder->id ?? null,
                'bidder_name' => $bidder->username ?? '',
                'bidder_address' => $params['bidderAddress'],
                'bid_price' => $params['bidPrice'],
                'currency' => $params['currency'],
                'currency_code' => $params['currency'],
                'currency_decimals' => 18, // Default precision: 18 decimals
                'chain' => $nft->chain,
                'expire_at' => $params['expireAt'],
                'status' => 'pending',
                'proof_type' => isset($params['proof']['type']) ? $params['proof']['type'] : null,
                'proof_value' => isset($params['proof']['value']) ? $params['proof']['value'] : null,
                'created_at' => time() * 1000
            ]);
            
            // Update NFT highest bid when this bid is higher
            if ($params['bidPrice'] > $nft->highest_bid) {
                $nft->highest_bid = $params['bidPrice'];
                $nft->save();
            }
            
            // Record activity entry
            Activities::create([
                'nft_id' => $nftId,
                'event_type' => 'bid',
                'from_address' => $params['bidderAddress'],
                'to_address' => $nft->owner_address,
                'price' => $params['bidPrice'],
                'currency' => $params['currency']
            ]);
            
            // Update user statistics
            if ($bidder) {
                \app\model\UserStats::where('user_id', $bidder->id)->inc('bids_count')->update();
            }
            
            $data = [
                'bidId' => $bid->id,
                'nftId' => $nftId,
                'bidderId' => $bidder->id ?? 0,
                'bidderName' => $bidder->username ?? '',
                'bidderAddress' => $params['bidderAddress'],
                'bidPrice' => (float)$params['bidPrice'],
                'currency' => $params['currency'],
                'createdAt' => $bid->created_at,
                'expireAt' => $params['expireAt'],
                'status' => 'pending'
            ];
            
            Db::commit();
            \Api::success($data, 200, 'Bid placed successfully');
            
        } catch (\think\exception\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            \Api::fail($e->getMessage(), 500);
        }
    }
    
    /**
     * Retrieve NFT bid list
     * GET /api/bids/list?nftId=xxx
     */
    public function list()
    {
        // New endpoint: GET /api/bids/list
        // nftId comes from query parameters
        $params = $this->getParam([
            'nftId' => 0,
            'status' => '',  // pending|approved|accepted|cancelled|expired
            'pageNum' => 1,
            'pageSize' => 20
        ], [
            'nftId' => 'require|integer'
        ], [
            'nftId.require' => 'NFT ID is required',
            'nftId.integer' => 'NFT ID must be an integer'
        ]);
        
        $nftId = $params['nftId'];
        
        $pageNum = max(1, intval($params['pageNum']));
        $pageSize = min(100, max(1, intval($params['pageSize'])));
        
        // Build query conditions
        $query = Bids::where('nft_id', $nftId);
        
        // Filter by status
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }
        
        // Paginate query results
        $list = $query->order('bid_price', 'desc')
            ->paginate([
                'list_rows' => $pageSize,
                'page' => $pageNum
            ]);
        
        // Assemble response payload
        $records = [];
        foreach ($list as $bid) {
            $records[] = [
                'bidId' => $bid->id,
                'nftId' => $bid->nft_id,
                'bidderId' => $bid->bidder_user_id,
                'bidderName' => $bid->bidder_name,
                'bidderAddress' => $bid->bidder_address,
                'bidPrice' => (float)$bid->bid_price,
                'currency' => $bid->currency,
                'createdAt' => $bid->created_at,
                'expireAt' => $bid->expire_at,
                'status' => $bid->status,
                'approvedAt' => $bid->approved_at
            ];
        }
        
        $data = [
            'records' => $records,
            'total' => $list->total(),
            'current' => $pageNum,
            'pages' => ceil($list->total() / $pageSize)
        ];
        
        \Api::success($data, 200, 'Success');
    }
    
    /**
     * Approve bid
     * POST /api/bids/{bidId}/approve
     */
    public function approve()
    {
        $bidId = $this->request->param('bidId');
        
        $params = $this->getParam([
            'ownerAddress' => ''
        ]);
        
        // Retrieve optional proof parameters
        $params['proof'] = $this->request->param('proof', []);
        
        Db::startTrans();
        try {
            // Retrieve bid
            $bid = Bids::find($bidId);
            if (!$bid) {
                \Api::fail('Bid not found', 404);
            }
            
            // Ensure the NFT exists
            $nft = Nfts::find($bid->nft_id);
            if (!$nft) {
                \Api::fail('NFT not found', 404);
            }
            
            // Verify requester owns the NFT when ownerAddress is provided
            if (!empty($params['ownerAddress']) && 
                $nft->owner_address != $params['ownerAddress']) {
                \Api::fail('Permission denied', 403);
            }
            
            if ($bid->status != 'pending') {
                \Api::fail('Invalid bid status', 400);
            }
            
            // Check whether the bid expired
            if ($bid->expire_at < time() * 1000) {
                \Api::fail('Bid expired', 400);
            }
            
            // Update bid status to approved
            $bid->status = 'approved';
            $bid->approved_at = time() * 1000;
            if (isset($params['proof']['delegateAddress'])) {
                $bid->delegate_address = $params['proof']['delegateAddress'];
            }
            if (isset($params['proof']['value'])) {
                $bid->proof_value = $params['proof']['value'];
            }
            $bid->save();
            
            // Update NFT price
            $nft->listing_price = $bid->bid_price;
            $nft->price = $bid->bid_price;
            $nft->price_currency = $bid->currency;
            $nft->save();
            
            // Record activity entry
            Activities::create([
                'nft_id' => $bid->nft_id,
                'event_type' => 'accept_bid',
                'from_address' => $nft->owner_address,
                'to_address' => $bid->bidder_address,
                'price' => $bid->bid_price,
                'currency' => $bid->currency
            ]);
            
            Db::commit();
            
            $data = [
                'bidId' => $bid->id,
                'nftId' => $bid->nft_id,
                'bidderId' => $bid->bidder_user_id,
                'bidderName' => $bid->bidder_name,
                'bidderAddress' => $bid->bidder_address,
                'bidPrice' => (float)$bid->bid_price,
                'currency' => $bid->currency,
                'createdAt' => $bid->created_at,
                'expireAt' => $bid->expire_at,
                'status' => 'approved',
                'approvedAt' => $bid->approved_at,
                'nftPriceUpdated' => [
                    'price' => (float)$nft->price,
                    'priceCurrency' => $nft->price_currency,
                    'lastSalePrice' => (float)$nft->last_sale_price,
                    'highestBid' => (float)$nft->highest_bid
                ]
            ];
            
            Db::commit();
            \Api::success($data, 200, 'Bid approved successfully and NFT price updated');
            
        } catch (\think\exception\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            \Api::fail($e->getMessage(), 500);
        }
    }
}


