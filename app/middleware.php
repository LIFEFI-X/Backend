<?php
// Global middleware definition file
return [
    // CORS middleware (must run first)
    \app\middleware\Cors::class,
    // Global request cache
    // \think\middleware\CheckRequestCache::class,
    // Load multilingual packs
    // \think\middleware\LoadLangPack::class,
    // Session initialization
    // \think\middleware\SessionInit::class
];

