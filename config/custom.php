<?php

return [
    'text2speech' => [
        'price' => env('TEXT2SPEECH_PRICE', 15.00)
    ],
    'eduix'       => [
        'username'     => env('EDU_IX_USERNAME'),
        'password'     => env('EDU_IX_PASSWORD'),
        'presharedkey' => env('EDU_IX_PRESHAREDKEY'),
    ],
    'shortcode' => [
        'link' => env('SHORTCODE_LINK','https://welcome.test-correct.nl/inv/'),
        'redirect' => env('SHORTCODE_REDIRECT','https://test-correct.nl/invite')
    ],
    'encrypt' => [
        'eckid_passphrase' => env('ECK_ID_PASSPHRASE','joepie'),
        'eckid_iv' => env('ECK_ID_IV',''),
    ]
];