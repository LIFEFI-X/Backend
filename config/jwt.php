<?php

return [
    'stores' => [
        // Single-application mode (empty app name)
        '' => [
            'token' => [
                // User unique identifier field (required)
                'unique_id_key' => 'user_id',
                
                // Signing key
                'signer_key' => env('jwt.secret', 'nft-marketplace-secret-key-2024'),
                
                // Signing algorithm
                'signer' => 'Lcobucci\JWT\Signer\Hmac\Sha256',
                
                // Token TTL (seconds)
                'expires_at' => env('jwt.ttl', 7200),
                
                // Refresh token TTL (seconds)
                'refresh_ttL' => env('jwt.refresh_ttl', 1209600),
                
                // Not-before interval (seconds)
                'not_before' => 0,
                
                // Token retrieval method
                'type' => 'Header',
                
                // Error code when token expires
                'relogin_code' => 50001,
                
                // Error code when token becomes invalid
                'refresh_code' => 50002,
                
                // Issuer
                'iss' => 'nft-api',
                
                // Audience
                'aud' => 'nft-client',
                
                // Automatic renewal
                'automatic_renewal' => false,
            ],
            'user' => [
                'bind' => false,
                'class' => null,
            ],
        ],
        // Alternate store (e.g., index application)
        'index' => [
            'token' => [
                'unique_id_key' => 'user_id',
                'signer_key' => env('jwt.secret', 'nft-marketplace-secret-key-2024'),
                'signer' => 'Lcobucci\JWT\Signer\Hmac\Sha256',
                'expires_at' => env('jwt.ttl', 7200),
                'refresh_ttL' => env('jwt.refresh_ttl', 1209600),
                'not_before' => 0,
                'type' => 'Header',
                'relogin_code' => 50001,
                'refresh_code' => 50002,
                'iss' => 'nft-api',
                'aud' => 'nft-client',
                'automatic_renewal' => false,
            ],
            'user' => [
                'bind' => false,
                'class' => null,
            ],
        ],
    ],
    'manager' => [
        // Cache prefix
        'prefix' => 'jwt',
        // Blacklist cache name
        'blacklist' => 'blacklist',
        // Whitelist cache name
        'whitelist' => 'whitelist',
    ],
];

