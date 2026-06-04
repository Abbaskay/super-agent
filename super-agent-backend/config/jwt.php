<?php

return [
    'secret' => env('JWT_SECRET'),
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'ttl' => (int) env('JWT_TTL', 1440), // minutes (24 hours)
    'cookie' => env('JWT_COOKIE', 'super_agent_token'),
    'domain' => env('JWT_COOKIE_DOMAIN', null),
];
