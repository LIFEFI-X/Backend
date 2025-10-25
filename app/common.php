<?php
// Application common functions.
if (!function_exists(''get_ip'')) {
    /**
     * Get the current user IP.
     * @return string
     */
    function get_ip(): string
    {
        return request()->ip();
    }
}