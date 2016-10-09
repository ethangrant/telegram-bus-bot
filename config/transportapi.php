<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transport Api App Key [REQUIRED]
    |--------------------------------------------------------------------------
    |
    | Your Transport Api App key is required for api access.
    | 'These are application keys used to authenticate requests.'
    |
    */
    'api_key' => env('API_KEY', '2f7d7752fa75a0da1d9670254a19fd70'),

    /*
    |--------------------------------------------------------------------------
    | Transport Api App ID [REQUIRED]
    |--------------------------------------------------------------------------
    |
    | Your Transport Api App ID is required for api access.
    | 'This is the application ID, you should send with each API request'
    |
    */
    'app_id' => env('APP_ID', 'e80b25cf'),
];