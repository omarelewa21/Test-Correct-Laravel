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
    ],
    'national_item_bank_school_customercode' => env('NATIONALITEMBANK_SCHOOL_CUSTOMERCODE','TBNI'),
    'national_item_bank_school_author' => env('NATIONALITEMBANK_SCHOOL_AUTHOR','info+ontwikkelaar@test-correct.nl'),
    'examschool_customercode' => env('EXAMSCHOOL_CUSTOMERCODE','OPENSOURCE1'),
    'examschool_author' => env('EXAMSCHOOL_AUTOR','info+CEdocent@test-correct.nl'),
    'default_trial_days' => env('DEFAULT_TRIAL_DAYS', 14),
];