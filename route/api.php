<?php

use think\facade\Route;

/**
 * NFT marketplace API routes
 */

// ==================== Authentication endpoints (no token required) ====================
// Login
Route::post('api/auth/connect/login', 'api.Auth/login');

// ==================== Extension endpoints (no token required) ====================
// Create knowledge transfer
Route::post('api/extension/transfer/create', 'api.Extension/createTransfer');

// Retrieve transfer data
Route::get('api/extension/transfer/:transferId', 'api.Extension/getTransfer');

// ==================== Marketplace endpoints (token required) ====================
Route::group(function() {
    // Fetch current authenticated user info
    Route::get('api/auth/user/info', 'api.Auth/currentUserInfo');
    
    // Marketplace NFT list
    Route::get('api/marketplace/nfts', 'api.Marketplace/nfts');
    
    // User details
    // Note: ':address' variant must come before ':userId' to avoid conflicts
    Route::get('api/users/address/:address', 'api.Auth/userInfo');
    Route::get('api/users/:userId', 'api.Auth/userInfo');
    
    // ==================== NFT endpoints ====================
    // Create NFT
    Route::post('api/nfts', 'api.Nft/create');
    
    // Purchase NFT
    Route::post('api/nfts/:nftId/purchase', 'api.Nft/purchase');
    
    // NFT details
    Route::get('api/nfts/:contractAddress', 'api.Nft/detail');
    Route::get('api/nfts/:contractAddress/:tokenId', 'api.Nft/detail');
    
    // ==================== Bid endpoints (dedicated routes to avoid NFT conflicts) ====================
    // Create bid - use dedicated route; nftId passed via parameters
    Route::post('api/bids/create', 'api.Bid/create');
    
    // Get NFT bid list - dedicated route; nftId passed via parameters
    Route::get('api/bids/list', 'api.Bid/list');
    
    // Approve bid
    Route::post('api/bids/:bidId/approve', 'api.Bid/approve');
    
    // ==================== Collection endpoints ====================
    // Create collection
    Route::post('api/collections', 'api.Collection/create');
    
})->middleware(\app\middleware\Auth::class);

// Route miss handler
Route::miss(function() {
    \Api::fail('API endpoint not found', 404);
});


