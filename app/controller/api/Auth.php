<?php

namespace app\controller\api;

use app\BaseController;
use app\model\Users;
use app\model\Wallets;
use app\model\Sessions;
use app\model\UserStats;
use xiaodi\JWTAuth\Facade\Jwt;
use think\facade\Db;

class Auth extends BaseController
{
    /**
     * User login
     * POST /api/auth/login
     */
    public function login()
    {
       
        $params = $this->getParam([
            ['provider', 'evm'],      // Login method
            ['address', ''],          // Wallet address
            'signature' => '',        // Signature
            'message' => '',          // Signature message
            'chainId' => 0,           // Chain ID
            'email' => '',            // Email
            'code' => ''              // Verification code
        ], [
            'provider' => 'require',
            'address' => 'require'
        ], [
            'provider.require' => 'Provider is required',
            'address.require' => 'Wallet address is required'
        ]);
        Db::startTrans();
        try {
            // Query or create user record
            $user = Users::where('address', $params['address'])->find();
          
            if (!$user) {
                // Create new user
                $user = Users::create([
                    'address' => $params['address'],
                    'username' => 'User_' . substr($params['address'], 0, 8),
                    'status' => 1,
                    'last_login_at' => date('Y-m-d H:i:s')
                ]);
                
                // Create primary wallet
                Wallets::create([
                    'user_id' => $user->id,
                    'wallet_address' => $params['address'],
                    'wallet_type' => $params['provider'],
                    'blockchain_network' => $params['provider'],
                    'is_primary' => 1,
                    'balance' => 0
                ]);
                
                // Initialize user statistics
                UserStats::create([
                    'user_id' => $user->id,
                    'nfts_owned' => 0,
                    'nfts_created' => 0,
                    'bids_count' => 0,
                    'purchases_count' => 0,
                    'sales_count' => 0,
                    'total_volume' => 0
                ]);
            } else {
                // Update last-login timestamp
                $user->last_login_at = date('Y-m-d H:i:s');
                $user->save();
            }
           
            // Generate JWT token
            $tokenObj = Jwt::token(['user_id' => $user->id, 'address' => $user->address]);
            $token = (string) $tokenObj;
       
            // Calculate expiration time
            $expiresIn = config('jwt.stores..token.expires_at', 7200); // Empty string used as the store name
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
            
            // Persist session record
          Sessions::create([
                'user_id' => $user->id,
                'access_token_hash' => md5($token),
                'provider' => $params['provider'],
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt
            ]);
            
            // Fetch wallet list
            $walletList = Wallets::where('user_id', $user->id)
                ->select()
                ->each(function($wallet) use ($user) {
                    return [
                        'id' => $wallet->id,
                        'userId' => $wallet->user_id,
                        'walletAddress' => $wallet->wallet_address,
                        'walletType' => $wallet->wallet_type,
                        'blockchainNetwork' => $wallet->blockchain_network,
                        'isPrimary' => (bool)$wallet->is_primary,
                        'balance' => (float)$wallet->balance,
                        'lastSyncAt' => $wallet->last_sync_at,
                        'version' => $wallet->version,
                        'isDeleted' => $wallet->is_deleted,
                        'createdAt' => $wallet->created_at,
                        'updatedAt' => $wallet->updated_at,
                        'avatarUrl' => $user->avatar_url,
                        'username' => $user->username,
                        'description' => $user->bio
                    ];
                })
                ->toArray();
            
            Db::commit();
            
            // Build response payload
            $data = [
                'accessToken' => $token,
                'refreshToken' => $token, // Simplified handling; ideally generate a dedicated refresh token
                'tokenType' => 'Bearer',
                'expiresIn' => $expiresIn,
                'scope' => 'user',
                'userInfo' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'displayName' => $user->display_name,
                    'avatarUrl' => $user->avatar_url,
                    'bio' => $user->bio,
                    'websiteUrl' => $user->website_url,
                    'socialLinks' => $user->social_links ?? '',
                    'userLevel' => $user->user_level,
                    'verificationStatus' => $user->verification_status,
                    'status' => $user->status,
                    'lastLoginAt' => $user->last_login_at,
                    'createdAt' => $user->created_at
                ],
                'walletInfo' => [
                    'walletId' => $user->id,
                    'username' => $user->username,
                    'address' => $user->address,
                    'avatarUrl' => $user->avatar_url,
                    'description' => $user->bio,
                    'websiteUrl' => $user->website_url,
                    'socialLinks' => $user->social_links ?? '',
                    'userLevel' => $user->user_level,
                    'verificationStatus' => $user->verification_status,
                    'status' => $user->status,
                    'lastLoginAt' => $user->last_login_at
                ],
                'walletList' => $walletList
            ];
            
            Db::commit();
            \Api::success($data, 200, 'Success');
            
        } catch (\think\exception\HttpResponseException $e) {
            // Normal response, rethrow
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            \Api::fail($e->getMessage(), 500);
        }
    }
    
    /**
     * Fetch the currently logged-in user information
     * GET /api/auth/user/info
     */
    public function currentUserInfo()
    {
        // Retrieve IDs injected by middleware
        $userId = $this->request->userId;
        $userAddress = $this->request->userAddress;
        
        if (!$userId) {
            \Api::fail('User information not found', 401);
        }
        
        $user = Users::with(['stats'])->find($userId);
        
        if (!$user) {
            \Api::fail('User not found', 404);
        }
        
        // Read token details from the current session
        $token = str_replace('Bearer ', '', $this->request->header('Authorization', ''));
        $session = Sessions::where('user_id', $userId)
            ->where('access_token_hash', md5($token))
            ->order('created_at', 'desc')
            ->find();
        
        $expiresIn = config('jwt.stores..token.expires_at', 7200);
        
        // Fetch wallet list
        $walletList = Wallets::where('user_id', $user->id)
            ->select()
            ->each(function($wallet) use ($user) {
                return [
                    'id' => $wallet->id,
                    'userId' => $wallet->user_id,
                    'walletAddress' => $wallet->wallet_address,
                    'walletType' => $wallet->wallet_type,
                    'blockchainNetwork' => $wallet->blockchain_network,
                    'isPrimary' => (bool)$wallet->is_primary,
                    'balance' => (float)$wallet->balance,
                    'lastSyncAt' => $wallet->last_sync_at,
                    'version' => $wallet->version,
                    'isDeleted' => $wallet->is_deleted,
                    'createdAt' => $wallet->created_at,
                    'updatedAt' => $wallet->updated_at,
                    'avatarUrl' => $user->avatar_url,
                    'username' => $user->username,
                    'description' => $user->bio
                ];
            })
            ->toArray();
        
        $data = [
            // Token information
            'accessToken' => $token,
            'refreshToken' => $token, // Simplified handling
            'tokenType' => 'Bearer',
            'expiresIn' => $expiresIn,
            'scope' => 'read write',
            
            // User profile fields (13 items)
            'userInfo' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'displayName' => $user->display_name,
                'avatarUrl' => $user->avatar_url,
                'bio' => $user->bio,
                'websiteUrl' => $user->website_url,
                'socialLinks' => $user->social_links ?? '',
                'userLevel' => $user->user_level,
                'verificationStatus' => $user->verification_status,
                'status' => $user->status,
                'lastLoginAt' => $user->last_login_at,
                'createdAt' => $user->created_at
            ],
            
            // Wallet info fields (11 items)
            'walletInfo' => [
                'walletId' => $user->id,
                'username' => $user->username,
                'address' => $user->address,
                'avatarUrl' => $user->avatar_url,
                'description' => $user->bio,
                'websiteUrl' => $user->website_url,
                'socialLinks' => $user->social_links ?? '',
                'userLevel' => $user->user_level,
                'verificationStatus' => $user->verification_status,
                'status' => $user->status,
                'lastLoginAt' => $user->last_login_at
            ],
            
            // Wallet list
            'walletList' => $walletList
        ];
        
        \Api::success($data, 200, 'Success');
    }
    
    /**
     * Fetch user details
     * GET /api/users/{userId}
     * GET /api/users/address/{address}
     */
    public function userInfo()
    {
        $userId = $this->request->param('userId');
        $address = $this->request->param('address');
        
        $query = Users::with(['stats']);
        
        if ($userId) {
            $user = $query->find($userId);
        } elseif ($address) {
            $user = $query->where('address', $address)->find();
        } else {
            \Api::fail('Invalid parameters', 400);
        }
        
        if (!$user) {
            \Api::fail('User not found', 404);
        }
        
        $stats = $user->stats ?? null;
        
        $data = [
            'id' => $user->id,
            'address' => $user->address,
            'username' => $user->username,
            'avatarUrl' => $user->avatar_url,
            'bio' => $user->bio,
            'websiteUrl' => $user->website_url,
            'socialLinks' => $user->social_links ?? '',
            'userLevel' => $user->user_level,
            'verificationStatus' => $user->verification_status,
            'createdAt' => $user->created_at,
            'stats' => [
                'nftsOwned' => $stats->nfts_owned ?? 0,
                'nftsCreated' => $stats->nfts_created ?? 0,
                'bids' => $stats->bids_count ?? 0,
                'purchases' => $stats->purchases_count ?? 0,
                'sales' => $stats->sales_count ?? 0,
                'totalVolume' => (float)($stats->total_volume ?? 0)
            ],
            'social' => [
                'twitter' => null,
                'discord' => null,
                'email' => $user->email
            ]
        ];
        
        \Api::success($data, 200, 'Success');
    }
}


