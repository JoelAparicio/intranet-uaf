<?php


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://intranet.uaf.gob.pa',  // ✅ NUEVO DOMINIO OFICIAL
        'http://172.19.115.44',        // ✅ MANTENER IP COMO FALLBACK
        'http://localhost:3000',       // ✅ MANTENER PARA DESARROLLO
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
