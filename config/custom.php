<?php

return [
    'text2speech' => [
        'price' => env('TEXT2SPEECH_PRICE', 15.00)
    ],
    'eduix'       => [
        'username'     => env('EDU_IX_USERNAME'),
        'password'     => env('EDU_IX_PASSWORD'),
        'presharedkey' => env('EDU_IX_PRESHAREDKEY'),
    ]
];