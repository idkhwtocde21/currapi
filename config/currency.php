<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exchange Rate API Configuration
    |--------------------------------------------------------------------------
    |
    | These values configure the third‑party exchange rate API used by the
    | application. In a full Laravel installation you should set the API key
    | in your .env file as CURRENCY_API_KEY.
    |
    */

    'api_key' => env('CURRENCY_API_KEY', 'b910accb9115300e759e16da'),

    'base_url' => env('CURRENCY_API_BASE_URL', 'https://v6.exchangerate-api.com/v6/'),
];
