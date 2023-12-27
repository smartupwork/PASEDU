<?php

return [
    'url' => env('PRESTASHOP_URL', env('PRESTASHOP_BASE_URL')),
    'token' => env('PRESTASHOP_TOKEN', env('PRESTASHOP_ACCESS_TOKEN')),
    'debug' => env('PRESTASHOP_DEBUG', env('PRESTASHOP_APP_DEBUG', false))
];
