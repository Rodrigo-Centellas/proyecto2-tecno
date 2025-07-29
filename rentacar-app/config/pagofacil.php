<?php

return [
    'base_url' => env('PAGOFACIL_BASE_URL', 'https://serviciostigomoney.pagofacil.com.bo'),
    'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret' => env('PAGOFACIL_TOKEN_SECRET'),
    'commerce_id' => env('PAGOFACIL_COMMERCE_ID'),
    'callback_url' => env('PAGOFACIL_CALLBACK_URL'),
    'return_url' => env('PAGOFACIL_RETURN_URL'),
];