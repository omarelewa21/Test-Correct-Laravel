<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"             => "The :attribute must be accepted.",
    "active_url"           => "The :attribute is not a valid URL.",
    "after"                => "The :attribute must be a date after :date.",
    "alpha"                => "The :attribute may only contain letters.",
    "alpha_dash"           => "The :attribute may only contain letters, numbers, and dashes.",
    "alpha_num"            => "The :attribute may only contain letters and numbers.",
    "array"                => "The :attribute must be an array.",
    "before"               => "The :attribute must be a date before :date.",
    "between"              => [
        "numeric" => "The :attribute must be between :min and :max.",
        "file"    => "The :attribute must be between :min and :max kilobytes.",
        "string"  => "The :attribute must be between :min and :max characters.",
        "array"   => "The :attribute must have between :min and :max items.",
    ],
    "boolean"              => "The :attribute field must be true or false.",
    "confirmed"            => "The :attribute confirmation does not match.",
    "date"                 => "The :attribute is not a valid date.",
    "date_format"          => "The :attribute does not match the format :format.",
    "different"            => "The :attribute and :other must be different.",
    "digits"               => "The :attribute must be :digits digits.",
    "digits_between"       => "The :attribute must be between :min and :max digits.",
    "email"                => "The :attribute must be a valid email address.",
    "filled"               => "The :attribute field is required.",
    "exists"               => "The selected :attribute is invalid.",
    "image"                => "The :attribute must be an image.",
    "in"                   => "The selected :attribute is invalid.",
    "integer"              => "The :attribute must be an integer.",
    "ip"                   => "The :attribute must be a valid IP address.",
    "max"                  => [
        "numeric" => "The :attribute may not be greater than :max.",
        "file"    => "The :attribute may not be greater than :max kilobytes.",
        "string"  => "The :attribute may not be greater than :max characters.",
        "array"   => "The :attribute may not have more than :max items.",
    ],
    "mimes"                => "The :attribute must be a file of type: :values.",
    "min"                  => [
        "numeric" => "The :attribute must be at least :min.",
        "file"    => "The :attribute must be at least :min kilobytes.",
        "string"  => "The :attribute must be at least :min characters.",
        "array"   => "The :attribute must have at least :min items.",
    ],
    "not_in"               => "The selected :attribute is invalid.",
    "numeric"              => "The :attribute must be a number.",
    'password'             => [
        'letters'       => 'The password field must contain at least one letter.',
        'mixed'         => 'The password field must contain at least one uppercase and one lowercase letter.',
        'numbers'       => 'The password field must contain at least one number.',
        'symbols'       => 'The password field must contain at least one symbol.',
        'uncompromised' => 'The given password has appeared in a data leak. Please choose a different password.',
    ],
    "regex"                => "The :attribute format is invalid.",
    "required"             => "The :attribute field is required.",
    "required_if"          => "The :attribute field is required when :other is :value.",
    "required_with"        => "The :attribute field is required when :values is present.",
    "required_with_all"    => "The :attribute field is required when :values is present.",
    "required_without"     => "The :attribute field is required when :values is not present.",
    "required_without_all" => "The :attribute field is required when none of :values are present.",
    "same"                 => "The :attribute and :other must match.",
    "size"                 => [
        "numeric" => "The :attribute must be :size.",
        "file"    => "The :attribute must be :size kilobytes.",
        "string"  => "The :attribute must be :size characters.",
        "array"   => "The :attribute must contain :size items.",
    ],
    "unique"               => "The :attribute has already been taken.",
    "url"                  => "The :attribute format is invalid.",
    "timezone"             => "The :attribute must be a valid zone.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],

        'epp' => [
            'max' => 'The total amount of points for this test is :max.
							You can’t fill out a amount greater than the total amount of points for this test.',
        ],

        'ppp' => [
            'max' => 'The total amount of points for this test is :max.
							You can’t fill out a amount greater than the total amount of points for this test.',
        ],

        'wanted_average' => [
            'between' => 'The average grade must have a value between :min and :max .'
        ],

        'n_term' => [
            'between' => 'The N term must have a value between :min and :max .'
        ],

        'pass_mark'              => [
            'between' => 'The Cesuur must have a value between :min and :max .'
        ],
        'is_examcoordinator_for' => [
            'required' => 'please choose one of classes options that exam coordinator can schedule exams for.',
            'in'       => 'please choose one of classes options that exam coordinator can schedule exams for.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'request.abbreviation' => 'abbreviation',
        'request.name'         => 'name',
        'request.introduction' => 'introduction-text',
        'request.weight'       => 'weight',
    ],

    'school_class_or_guest_accounts_required' => 'Select a school class or the guest account option.',
    'test name'                               => 'test name',
    'unique_test_name'                        => 'The given test name is already in use. Please enter a unique test name.',

    'invigilator_required' => 'It is required to choose an invigilator.',

    // NIST Password Rules
    'can-not-contain-word'              => 'The password can not contain the word \':word\'.',
    'can-not-be-similar-to-word'        => 'The password can not be similar to the word \':word\'.',
    'found-in-data-breach'              => 'The password was found in a third party data breach, and can not be used.',
    'can-not-be-dictionary-word'        => 'The password can not be a dictionary word.',
    'can-not-be-repetitive-characters'  => 'The password can not be repetitive characters.',
    'can-not-be-sequential-characters'  => 'The password can not be sequential characters.',

    'word_import_empty_values' => '{1} The row :rows has invalid values|[2,*] The rows :rows have invalid values'
];
