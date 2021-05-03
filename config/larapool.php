<?php

return array(

    //-------------------------------
    // Timezone for insert dates in database
    // If you want PoolPort not set timezone, just leave it empty
    //--------------------------------
    'timezone' => 'Asia/Tehran',

    //--------------------------------
    // default transaction platform
    // web or mobile
    //--------------------------------
    'default_platform' => 'web',

    //--------------------------------
    // Sign pay hash, use in hashing
    //--------------------------------
    'sign-pay' => env('LARAPOOL_SIGN_KEY', 'your-sign-key'),

    //--------------------------------
    // Soap configuration
    //--------------------------------
    'soap' => array(
        'attempts' => 2 // Attempts if soap connection is fail
    ),

    //--------------------------------
    // Database configuration
    //--------------------------------
    'database' => array(
        'host' => env('DB_HOST', 'localhost'),
        'dbname' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'create' => false // For first time you must set this to true for create tables in database
    ),

    //--------------------------------
    // Zarinpal gateway
    //--------------------------------
    'zarinpal' => array(
        'merchant-id' => '',
        'type' => 'normal', // (zarin-gate || normal)
        'callback-url' => '',
        'server' => 'iran', // (germany || iran)
        'email' => '',
        'mobile' => '',
        'description' => '',
    ),

    //--------------------------------
    // Mellat gateway
    //--------------------------------
    'mellat' => array(
        'username' => '',
        'password' => '',
        'terminalId' => 0000000,
        'callback-url' => 'http://www.example.org/result',
    ),

    //--------------------------------
    // Payline gateway
    //--------------------------------
    'payline' => array(
        'api' => 'xxxxx-xxxxx-xxxxx-xxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'callback-url' => 'http://www.example.org/result',
    ),

    //--------------------------------
    // Sadad gateway
    //--------------------------------
    'sadad' => array(
        'merchant' => '',
        'transactionKey' => '',
        'terminalId' => 000000000,
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // JahanPay gateway
    //--------------------------------
    'jahanpay' => array(
        'api' => 'xxxxxxxxxxx',
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // Parsian gateway
    //--------------------------------
    'parsian' => array(
        'pin' => 'xxxxxxxxxxxxxxxxxxxx',
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // Pasargad gateway
    //--------------------------------
    'pasargad' => array(
        'merchant-code' => '9999999',
        'terminal-code' => '999999',
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // Saderat gateway
    //--------------------------------
    'saderat' => array(
        'merchant-id' => '999999999999999',
        'terminal-id' => '99999999',
        'public-key' => __DIR__ . '/saderat-public-key.pem',
        'private-key' => __DIR__ . '/saderat-private-key.pem',
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // IranKish gateway
    //--------------------------------
    'irankish' => array(
        'merchant-id' => 'xxxx',
        'sha1-key' => 'xxxxxxxxxxxxxxxxxxxx',
        'description' => 'description',
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // Simulator gateway
    //--------------------------------
    'simulator' => array(
        'callback-url' => 'http://example.org/result',
    ),

    //--------------------------------
    // Saman gateway
    //--------------------------------
    'saman' => array(
        'merchant-id' => 'xxxxx',
        'callback-url' => 'http://example.org/result'
    ),

    // Pay gateway
    //--------------------------------
    'pay' => array(
        'api' => 'ad17e5bf1f0d91ec687b7c8bbe29de59',
        'callback-url' => 'localhost:8000/api/v1/payment/',
    ),

    // JiBit gateway
    //--------------------------------
    'jibit' => array(
        'merchant-id' => '7892',
        'password' => 'vU9tKtxRkupE9ZMg',
        'callback-url' => 'localhost:8000/api/v1/payment',
        'user-mobile' => '09196805049',
    ),

    // AP gateway
    //--------------------------------
    'ap' => array(
        'merchant-config-id' => 'xxxx',
        'username' => 'xxxxxxxxxx',
        'password' => 'xxxxxxxxxx',
        'callback-url' => 'http://www.example.org/result',
        'encryption-key' => 'xxxxxxxxxx',
        'encryption-vector' => 'xxxxxxxxxx',
        'sync-time' => false,
        'user-mobile' => '09xxxxxxxxx'
    ),

    // BitPay gateway
    //--------------------------------
    'bitpay' => array(
        'api' => 'xxxxx-xxxxx-xxxxx-xxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'callback-url' => 'http://www.example.org/result',
        'name' => 'xxxxxxxxxx',
        'email' => 'email@gmail.com',
        'description' => 'description',
        'user-mobile' => '09xxxxxxxx'
    ),

    // IDPay gateway
    //--------------------------------
    'idpay' => array(
        'api' => 'x-x-x-x-x',
        'callback-url' => 'http://www.example.org/result',
        'sandbox'=> false,
        'name' => 'name',
        'email' => 'email',
        'description' => 'description',
        'user-mobile' => '09xxxxxxxx',
    ),

    // PayPing gateway
    //--------------------------------
    'payping' => array(
        'token' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'user-mobile' => '09xxxxxxxx',
        'callback-url' => 'http://www.example.org/result',
    ),
);
