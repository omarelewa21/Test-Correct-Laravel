<?php

return [
    'text2speech'                            => [
        'price' => env('TEXT2SPEECH_PRICE', 15.00)
    ],
    'eduix'                                  => [
        'username'     => env('EDU_IX_USERNAME'),
        'password'     => env('EDU_IX_PASSWORD'),
        'presharedkey' => env('EDU_IX_PRESHAREDKEY'),
    ],
    'shortcode'                              => [
        'link'     => env('SHORTCODE_LINK', 'https://welcome.test-correct.nl/inv/'),
        'redirect' => env('SHORTCODE_REDIRECT', 'https://test-correct.nl/invite')
    ],
    'encrypt'                                => [
        'eckid_passphrase' => env('ECK_ID_PASSPHRASE', 'joepie'),
        'eckid_iv'         => env('ECK_ID_IV', ''),
    ],
    'national_item_bank_school_customercode' => env('NATIONALITEMBANK_SCHOOL_CUSTOMERCODE', 'TBNI'),
    'national_item_bank_school_author'       => env('NATIONALITEMBANK_SCHOOL_AUTHOR', 'info+ontwikkelaar@test-correct.nl'),
    'thieme_meulenhoff_school_customercode'  => env('THIEMEMEULENHOFFITEMBANK_SCHOOL_CUSTOMERCODE', 'THIEMEMEULENHOFF'),
    'thieme_meulenhoff_school_author'        => env('THIEMEMEULENHOFFITEMBANK_SCHOOL_AUTHOR', 'info+tmontwikkelaar@test-correct.nl'),
    'creathlon_school_customercode'          => env('CREATHLONITEMBANK_SCHOOL_CUSTOMERCODE', 'CREATHLON'),
    'creathlon_school_author'                => env('CREATHLONITEMBANK_SCHOOL_AUTHOR', 'info+creathlonontwikkelaar@test-correct.nl'),
    'formidable_school_customercode'          => env('FORMIDABLEONITEMBANK_SCHOOL_CUSTOMERCODE', 'FORMIDABLE'),
    'formidable_school_author'                => env('FORMIDABLEITEMBANK_SCHOOL_AUTHOR', 'info+fdontwikkelaar@test-correct.nl'),
    'olympiade_school_customercode'          => env('OLYMPIADEITEMBANK_SCHOOL_CUSTOMERCODE', 'SBON'),
    'olympiade_school_author'                => env('OLYMPIADEITEMBANK_SCHOOL_AUTHOR', 'info+olympiadeontwikkelaar@test-correct.nl'),
    'examschool_customercode'                => env('EXAMSCHOOL_CUSTOMERCODE', 'OPENSOURCE1'),
    'examschool_author'                      => env('EXAMSCHOOL_AUTOR', 'info+CEdocent@test-correct.nl'),
    'default_trial_days'                     => env('DEFAULT_TRIAL_DAYS', 14),
    'default_general_terms_days'             => env('DEFAULT_GENERAL_TERMS_DAYS', 14),
    'enable_additional_seeders'              => env('ENABLE_ADDITIONAL_SEEDERS', true),
    'TB_customer_code'                       => env('TB_CUSTOMER_CODE', 'TBSC'),

    'enable_hsts'                            => env('ENABLE_HSTS', true),
];