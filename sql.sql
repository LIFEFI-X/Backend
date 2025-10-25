-- ==========================================
-- MySQL 8.0+ NFT marketplace database schema
-- Table prefix: nft_
-- ==========================================

-- ==========================================
-- 1. User and authentication tables
-- ==========================================

-- User base table
CREATE TABLE `nft_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `address` VARCHAR(128) NOT NULL COMMENT 'Primary wallet address (lowercase)',
  `username` VARCHAR(64) NULL,
  `email` VARCHAR(128) NULL,
  `display_name` VARCHAR(128) NULL,
  `avatar_url` VARCHAR(512) NULL,
  `bio` VARCHAR(1024) NULL,
  `website_url` VARCHAR(512) NULL,
  `social_links` JSON NULL COMMENT 'Social links JSON',
  `user_level` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'User level',
  `verification_status` TINYINT NOT NULL DEFAULT 0 COMMENT '0-unverified 1-verified',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1-active 0-disabled',
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_address` (`address`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User base info table';

-- Sessions table
CREATE TABLE `nft_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `access_token_hash` VARCHAR(255) NOT NULL COMMENT 'Token hash value',
  `refresh_token_hash` VARCHAR(255) NULL,
  `provider` VARCHAR(32) NOT NULL COMMENT 'evm/solana/email/discord/twitter',
  `token_type` VARCHAR(32) NOT NULL DEFAULT 'Bearer',
  `expires_at` DATETIME NOT NULL,
  `scope` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_access_token` (`access_token_hash`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User sessions table';

-- Wallets table (supports multiple wallets)
CREATE TABLE `nft_wallets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `wallet_address` VARCHAR(128) NOT NULL COMMENT 'Wallet address (lowercase)',
  `wallet_type` VARCHAR(16) NOT NULL COMMENT 'evm/solana',
  `blockchain_network` VARCHAR(64) NOT NULL COMMENT 'Blockchain network identifier',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is primary wallet',
  `balance` DECIMAL(38,18) NULL DEFAULT 0 COMMENT 'Balance',
  `last_sync_at` DATETIME NULL,
  `version` INT UNSIGNED NOT NULL DEFAULT 1,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wallet_address` (`wallet_address`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `fk_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User wallets table';

-- ==========================================
-- 2. Collection table
-- ==========================================

CREATE TABLE `nft_collections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `creator_user_id` BIGINT UNSIGNED NULL COMMENT 'Creator user ID',
  `creator_address` VARCHAR(128) NOT NULL COMMENT 'Creator address',
  
  -- Basic information
  `name` VARCHAR(128) NOT NULL,
  `symbol` VARCHAR(32) NULL,
  `description` TEXT NULL,
  `short_url` VARCHAR(64) NULL COMMENT 'Short link (unique)',
  `image_url` VARCHAR(512) NULL COMMENT 'Cover image URL',
  `banner_image_url` VARCHAR(512) NULL COMMENT 'Banner image URL',
  `category` VARCHAR(64) NULL COMMENT 'Category',
  `project_url` VARCHAR(512) NULL COMMENT 'Project website',
  
  -- On-chain information
  `chain` VARCHAR(16) NOT NULL COMMENT 'evm/solana/bsc',
  `contract_or_mint` VARCHAR(128) NOT NULL COMMENT 'Contract or program address',
  `create_tx_hash` VARCHAR(128) NULL COMMENT 'Creation transaction hash',
  
  -- Mint configuration
  `mint_price` DECIMAL(38,18) NULL DEFAULT 0,
  `price_units` VARCHAR(16) NULL COMMENT '1-ETH 2-SOL 3-USDT',
  `royalty_fee` DECIMAL(5,2) NULL DEFAULT 0 COMMENT 'Royalty fee percentage',
  `royalty_percentage` DECIMAL(5,2) NULL DEFAULT 0,
  `max_supply` INT UNSIGNED NULL DEFAULT 0,
  `mint_limit` INT UNSIGNED NULL DEFAULT 0,
  
  -- Statistics
  `floor_price` DECIMAL(38,18) NULL DEFAULT 0,
  `total_volume` DECIMAL(38,18) NULL DEFAULT 0,
  `total_items` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','hidden','blocked') NOT NULL DEFAULT 'active',
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chain_contract` (`chain`, `contract_or_mint`),
  UNIQUE KEY `uk_short_url` (`short_url`),
  KEY `idx_creator_user` (`creator_user_id`),
  KEY `idx_creator_address` (`creator_address`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_collections_creator` FOREIGN KEY (`creator_user_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT collection table';

-- ==========================================
-- 3. NFT asset table
-- ==========================================

CREATE TABLE `nft_nfts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  
  -- Related information
  `collection_id` BIGINT UNSIGNED NOT NULL,
  `owner_user_id` BIGINT UNSIGNED NULL COMMENT 'Owner user ID',
  `owner_address` VARCHAR(128) NOT NULL COMMENT 'Owner address',
  `creator_address` VARCHAR(128) NOT NULL COMMENT 'Creator address',
  
  -- Basic information
  `name` VARCHAR(256) NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(512) NULL,
  `display_image_url` VARCHAR(512) NULL COMMENT 'Thumbnail URL',
  `animation_url` VARCHAR(512) NULL COMMENT 'Animation URL',
  `display_animation_url` VARCHAR(512) NULL,
  `external_url` VARCHAR(512) NULL,
  
  -- On-chain information
  `chain` VARCHAR(16) NOT NULL COMMENT 'evm/solana/bsc',
  `token_id` VARCHAR(128) NULL COMMENT 'Token ID',
  `token_id_or_mint` VARCHAR(128) NOT NULL COMMENT 'Token ID or mint address',
  `contract_address` VARCHAR(128) NOT NULL COMMENT 'NFT contract address',
  `blockchain_network` VARCHAR(64) NOT NULL,
  `token_standard` VARCHAR(32) NULL COMMENT 'ERC721/ERC1155/SPL',
  `metadata_url` VARCHAR(512) NULL COMMENT 'Metadata URI',
  `metadata_uri` VARCHAR(512) NULL,
  `mint_tx_hash` VARCHAR(128) NULL COMMENT 'Mint transaction hash',
  `mint_transaction_hash` VARCHAR(128) NULL,
  `mint_timestamp` DATETIME NULL,
  
  -- Attributes and rarity
  `attributes` JSON NULL COMMENT 'Attributes JSON array',
  `rarity_rank` INT UNSIGNED NULL,
  `rarity_score` DECIMAL(10,2) NULL,
  
  -- Pricing snapshot (real-time prices stored in listings table)
  `price` DECIMAL(38,18) NULL DEFAULT 0,
  `price_currency` VARCHAR(16) NULL,
  `usd_price` DECIMAL(18,2) NULL,
  `last_sale_price` DECIMAL(38,18) NULL,
  `listing_price` DECIMAL(38,18) NULL,
  `highest_bid` DECIMAL(38,18) NULL,
  `lowest_ask` DECIMAL(38,18) NULL,
  `is_listed` TINYINT(1) NOT NULL DEFAULT 0,
  `marketplace` VARCHAR(64) NULL,
  `category` VARCHAR(64) NULL,
  `last_sale_timestamp` DATETIME NULL,
  
  -- Pricing details (JSON stores complex pricing structure)
  `price_info` JSON NULL COMMENT 'Detailed pricing info',
  
  `status` ENUM('normal','burned','locked','transferred') NOT NULL DEFAULT 'normal',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_collection_token` (`collection_id`, `token_id_or_mint`),
  UNIQUE KEY `uk_contract_address` (`contract_address`),
  KEY `idx_collection` (`collection_id`),
  KEY `idx_owner_user` (`owner_user_id`),
  KEY `idx_owner_address` (`owner_address`),
  KEY `idx_creator_address` (`creator_address`),
  KEY `idx_chain` (`chain`),
  KEY `idx_is_listed` (`is_listed`),
  KEY `idx_marketplace` (`marketplace`),
  KEY `idx_price` (`price`),
  CONSTRAINT `fk_nfts_collection` FOREIGN KEY (`collection_id`) REFERENCES `nft_collections`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_nfts_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT asset table';

-- NFT stats table
CREATE TABLE `nft_nft_stats` (
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'View count',
  `favorites` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Favorite count',
  `last_viewed_at` DATETIME NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nft_id`),
  CONSTRAINT `fk_nft_stats_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT statistics table';

-- ==========================================
-- 4. Marketplace transaction tables
-- ==========================================

-- Listings table
CREATE TABLE `nft_listings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `seller_user_id` BIGINT UNSIGNED NULL,
  `seller_address` VARCHAR(128) NOT NULL,
  
  -- Pricing data (store atomic units using DECIMAL(65,0))
  `price` DECIMAL(38,18) NOT NULL COMMENT 'Listing price (decimal)',
  `price_raw` VARCHAR(128) NULL COMMENT 'Atomic unit price string (large value)',
  `currency_code` VARCHAR(16) NOT NULL COMMENT 'SOL/ETH/USDT',
  `currency_decimals` TINYINT UNSIGNED NOT NULL DEFAULT 18,
  `usd_price` DECIMAL(18,2) NULL,
  
  `chain` VARCHAR(16) NOT NULL,
  `marketplace` VARCHAR(64) NULL COMMENT 'Marketplace name',
  `expiration` DATETIME NULL COMMENT 'Expiration time',
  `status` ENUM('active','sold','cancelled','expired') NOT NULL DEFAULT 'active',
  `is_active` TINYINT(1) GENERATED ALWAYS AS (CASE WHEN `status` = 'active' THEN 1 ELSE 0 END) STORED,
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_active_per_nft` (`nft_id`, `is_active`),
  KEY `idx_status_created` (`status`, `created_at`),
  KEY `idx_status_price` (`status`, `price`),
  KEY `idx_chain_status` (`chain`, `status`),
  KEY `idx_seller` (`seller_user_id`),
  CONSTRAINT `fk_listings_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_listings_seller` FOREIGN KEY (`seller_user_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT listings table';

-- Bids table
CREATE TABLE `nft_bids` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bid_id` BIGINT UNSIGNED NOT NULL COMMENT 'Business bid ID',
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `bidder_id` BIGINT UNSIGNED NULL COMMENT 'Bidder user ID',
  `bidder_user_id` BIGINT UNSIGNED NULL,
  `bidder_name` VARCHAR(128) NULL,
  `bidder_address` VARCHAR(128) NOT NULL,
  
  -- Pricing information
  `bid_price` DECIMAL(38,18) NOT NULL COMMENT 'Bid amount',
  `price_raw` VARCHAR(128) NULL COMMENT 'Atomic unit price string',
  `currency` VARCHAR(16) NOT NULL COMMENT 'SOL/ETH/USDT',
  `currency_code` VARCHAR(16) NOT NULL,
  `currency_decimals` TINYINT UNSIGNED NOT NULL DEFAULT 18,
  
  `chain` VARCHAR(16) NOT NULL,
  `expire_at` BIGINT UNSIGNED NOT NULL COMMENT 'Expiration timestamp (ms)',
  `status` ENUM('pending','approved','accepted','cancelled','expired') NOT NULL DEFAULT 'pending',
  
  -- On-chain proof
  `proof_type` ENUM('signature','tx') NULL,
  `proof_value` VARCHAR(256) NULL COMMENT 'Signature or transaction hash',
  `delegate_address` VARCHAR(128) NULL COMMENT 'Delegated address (after approval)',
  `approved_at` BIGINT UNSIGNED NULL COMMENT 'Approval timestamp',
  
  `created_at` BIGINT UNSIGNED NOT NULL COMMENT 'Creation timestamp (ms)',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bid_id` (`bid_id`),
  KEY `idx_nft_status_price` (`nft_id`, `status`, `bid_price`),
  KEY `idx_bidder_created` (`bidder_user_id`, `created_at`),
  KEY `idx_expire` (`expire_at`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_bids_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_bids_bidder` FOREIGN KEY (`bidder_user_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT bids table';

-- Orders table
CREATE TABLE `nft_orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` VARCHAR(64) NOT NULL COMMENT 'Business order ID',
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `listing_id` BIGINT UNSIGNED NULL COMMENT 'Associated listing ID',
  `bid_id` BIGINT UNSIGNED NULL COMMENT 'Associated bid ID',
  
  -- Buyer and seller
  `buyer_user_id` BIGINT UNSIGNED NOT NULL,
  `buyer_address` VARCHAR(128) NOT NULL,
  `seller_user_id` BIGINT UNSIGNED NOT NULL,
  `seller_address` VARCHAR(128) NOT NULL,
  
  -- Pricing information
  `price` DECIMAL(38,18) NOT NULL,
  `price_raw` VARCHAR(128) NULL COMMENT 'Atomic unit price string',
  `currency_code` VARCHAR(16) NOT NULL,
  `currency_decimals` TINYINT UNSIGNED NOT NULL DEFAULT 18,
  
  `chain` VARCHAR(16) NOT NULL,
  `tx_hash` VARCHAR(128) NOT NULL COMMENT 'Transaction hash',
  `transfer_method` ENUM('direct_buy','delegate_transfer') NOT NULL COMMENT 'Purchase method',
  `status` ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
  
  -- Ownership change log
  `previous_owner` VARCHAR(128) NULL,
  `new_owner` VARCHAR(128) NULL,
  `ownership_updated_at` DATETIME NULL,
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_id` (`order_id`),
  UNIQUE KEY `uk_tx_hash` (`tx_hash`),
  KEY `idx_buyer_created` (`buyer_user_id`, `created_at`),
  KEY `idx_seller_created` (`seller_user_id`, `created_at`),
  KEY `idx_nft_created` (`nft_id`, `created_at`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_orders_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_listing` FOREIGN KEY (`listing_id`) REFERENCES `nft_listings`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_bid` FOREIGN KEY (`bid_id`) REFERENCES `nft_bids`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_seller` FOREIGN KEY (`seller_user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT orders table';

-- ==========================================
-- 5. Activity log table
-- ==========================================

CREATE TABLE `nft_activities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `event_type` ENUM('mint','transfer','sale','listing','bid','cancel_listing','accept_bid') NOT NULL,
  `from_address` VARCHAR(128) NULL,
  `to_address` VARCHAR(128) NULL,
  `price` DECIMAL(38,18) NULL,
  `currency` VARCHAR(16) NULL,
  `tx_hash` VARCHAR(128) NULL,
  `marketplace` VARCHAR(64) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nft_event` (`nft_id`, `event_type`),
  KEY `idx_from_address` (`from_address`),
  KEY `idx_to_address` (`to_address`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_activities_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT activity log table';

-- ==========================================
-- 6. User favorites and follows table
-- ==========================================

CREATE TABLE `nft_user_favorites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_nft` (`user_id`, `nft_id`),
  KEY `idx_nft` (`nft_id`),
  CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_favorites_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User favorites table';

CREATE TABLE `nft_user_follows` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `follower_id` BIGINT UNSIGNED NOT NULL COMMENT 'Follower ID',
  `following_id` BIGINT UNSIGNED NOT NULL COMMENT 'Following ID',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_follower_following` (`follower_id`, `following_id`),
  KEY `idx_following` (`following_id`),
  CONSTRAINT `fk_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_follows_following` FOREIGN KEY (`following_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User follows table';

-- ==========================================
-- 7. Auxiliary tables
-- ==========================================

-- User stats table
CREATE TABLE `nft_user_stats` (
  `user_id` BIGINT UNSIGNED NOT NULL,
  `nfts_owned` INT UNSIGNED NOT NULL DEFAULT 0,
  `nfts_created` INT UNSIGNED NOT NULL DEFAULT 0,
  `bids_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `purchases_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `sales_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_volume` DECIMAL(38,18) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_stats_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User stats table';

-- Price history table
CREATE TABLE `nft_price_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nft_id` BIGINT UNSIGNED NOT NULL,
  `price` DECIMAL(38,18) NOT NULL,
  `currency` VARCHAR(16) NOT NULL,
  `event_type` ENUM('listing','sale','bid') NOT NULL,
  `tx_hash` VARCHAR(128) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nft_created` (`nft_id`, `created_at`),
  CONSTRAINT `fk_price_history_nft` FOREIGN KEY (`nft_id`) REFERENCES `nft_nfts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='NFT price history table';


