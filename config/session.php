<?php
// +----------------------------------------------------------------------
// | Session settings
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'PHPSESSID',
    // Submission variable for SESSION_ID to resolve Flash upload cross-domain issues
    'var_session_id' => '',
    // Driver type (supports file or cache)
    'type'           => 'file',
    // Storage connection identifier (effective when using cache driver)
    'store'          => null,
    // Expiration time
    'expire'         => 1440,
    // Prefix
    'prefix'         => '',
];

