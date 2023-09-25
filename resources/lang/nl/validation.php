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

    'accepted'             => ':Attribute dient te worden geaccepteerd.',
    'active_url'           => ':Attribute is geen geldige URL.',
    'after'                => ':Attribute moet een datum zijn na :date.',
    'after_or_equal'       => ':Attribute moet een datum zijn na of gelijk aan :date.',
    'alpha'                => ':Attribute mag alleen letters bevatten.',
    'alpha_dash'           => ':Attribute mag alleen letters, nummers, en strepen bevatten.',
    'alpha_num'            => ':Attribute mag alleen letters en nummers bevatten.',
    'array'                => ':Attribute moet een array zijn.',
    'before'               => ':Attribute moet een datum zijn eerder dan :date.',
    'before_or_equal'      => ':Attribute moet een datum zijn voor of gelijk aan :date.',
    'between'              => [
        'numeric' => ':Attribute moet tussen :min en :max liggen.',
        'file'    => ':Attribute moet tussen :min en :max kilobytes zijn.',
        'string'  => ':Attribute moet tussen :min en :max karakters lang zijn.',
        'array'   => ':Attribute moet tussen :min en :max items bevatten.',
    ],
    'boolean'              => ':Attribute kan enkel waar of niet waar zijn.',
    'confirmed'            => ':Attribute bevestiging komt niet overeen.',
    'date'                 => ':Attribute is geen geldige datum.',
    'date_equals'          => ':Attribute moet een datum zijn gelijk aan :date.',
    'date_format'          => ':Attribute komt niet overeen met het formaat :format.',
    'different'            => ':Attribute en :other dienen verschillend te zijn.',
    'digits'               => ':Attribute moet :digits cijfers zijn.',
    'digits_between'       => ':Attribute moet tussen :min en :max cijfers zijn.',
    'dimensions'           => ':Attribute heeft een ongeldige grootte.',
    'distinct'             => ':Attribute heeft een dubbele waarde.',
    'email'                => ':Attribute dient een geldig emailadres te zijn.',
    'ends_with'            => ':Attribute moet eindigen met één van het volgende: :values',
    'exists'               => 'Geselecteerde :attribute is ongeldig.',
    'file'                 => ':Attribute moet een bestand zijn.',
    'filled'               => ':Attribute veld is verplicht.',
    'gt'                   => [
        'numeric' => 'Het :attribute veld moet groter zijn dan :value.',
        'file'    => 'Het :attribute veld moet groter zijn dan :value kilobytes.',
        'string'  => 'Het :attribute veld moet groter zijn dan :value tekens.',
        'array'   => 'Het :attribute veld moet meer dan :value items bevatten.',
    ],
    'gte'                  => [
        'numeric' => 'Het :attribute veld moet groter of gelijk zijn aan :value.',
        'file'    => 'Het :attribute veld moet groter of gelijk zijn aan :value kilobytes.',
        'string'  => 'Het :attribute veld moet groter of gelijk zijn aan :value tekens.',
        'array'   => 'Het :attribute veld moet :value of meer items bevatten.',
    ],
    'image'                => ':Attribute dient een afbeelding te zijn.',
    'in'                   => 'Geselecteerde :attribute is ongeldig.',
    'in_array'             => ':Attribute komt niet voor in :other.',
    'integer'              => ':Attribute dient een geheel getal te zijn.',
    'ip'                   => ':Attribute dient een geldig IP adres te zijn.',
    'ipv4'                 => ':Attribute dient een geldig IPv4 adres te zijn.',
    'ipv6'                 => ':Attribute dient een geldig IPv6 adres te zijn..',
    'json'                 => ':Attribute moet een geldige JSON string zijn.',
    'lt'                   => [
        'numeric' => 'Het :attribute veld moet kleiner zijn dan :value.',
        'file'    => 'Het :attribute veld moet kleiner zijn dan :value kilobytes.',
        'string'  => 'Het :attribute veld moet kleiner zijn dan :value tekens.',
        'array'   => 'Het :attribute veld moet minder dan :value items bevatten.',
    ],
    'lte'                  => [
        'numeric' => 'Het :attribute veld moet kleiner of gelijk zijn aan :value.',
        'file'    => 'Het :attribute veld moet kleiner of gelijk zijn aan :value kilobytes.',
        'string'  => 'Het :attribute veld moet kleiner of gelijk zijn aan :value tekens.',
        'array'   => 'Het :attribute veld mag maximaal :value items bevatten.',
    ],
    'max'                  => [
        'numeric' => ':Attribute mag niet groter zijn dan :max.',
        'file'    => ':Attribute mag niet groter zijn dan :max kilobytes.',
        'string'  => ':Attribute mag niet groter zijn dan :max karakters.',
        'array'   => ':Attribute mag niet meer dan :max items bevatten.',
    ],
    'mimes'                => ':Attribute dient een bestand te zijn van het type: :values.',
    'mimetypes'            => ':Attribute dient een bestand te zijn van het type: :values.',
    'min'                  => [
        'numeric' => ':Attribute dient minimaal :min te zijn.',
        'file'    => ':Attribute dient minimaal :min kilobytes te zijn.',
        'string'  => ':Attribute dient minimaal :min karakters te bevatten.',
        'array'   => ':Attribute dient minimaal :min items te bevatten.',
    ],
    'not_in'               => 'Geselecteerde :attribute is ongeldig.',
    'not_regex'            => 'Het :attribute format is ongeldig.',
    'numeric'              => ':Attribute dient een nummer te zijn.',
    'password'             => [
        'letters'       => 'Het veld wachtwoord moet minstens één letter bevatten.',
        'mixed'         => 'Het veld wachtwoord moet minstens één hoofdletter en één kleine letter bevatten.',
        'numbers'       => 'Het veld wachtwoord moet minstens één nummer bevatten.',
        'symbols'       => 'Het veld wachtwoord moet minstens één symbool bevatten.',
        'uncompromised' => 'Het opgegeven wachtwoord is verschenen in een datalek. Kies alstublieft een ander wachtwoord.',
    ],
    'present'              => ':Attribute dient aanwezig te zijn.',
    'regex'                => 'Het :attribute formaat is ongeldig.',
    'required'             => 'Het :attribute veld is verplicht.',
    'required_if'          => 'Het :attribute veld is verplicht wanneer :other is :value.',
    'required_unless'      => 'Het :attribute veld is verplicht, tenzij :other is in :values.',
    'required_with'        => 'Het :attribute veld is verplicht wanneer :values aanwezig is.',
    'required_with_all'    => 'Het :attribute veld is verplicht wanneer :values aanwezig is.',
    'required_without'     => 'Het :attribute veld is verplicht wanneer :values niet aanwezig is.',
    'required_without_all' => 'Het :attribute veld is verplicht wanneer geen van :values aanwezig is.',
    'same'                 => ':Attribute en :other moeten hetzelfde zijn.',
    'size'                 => [
        'numeric' => ':Attribute moet :size zijn.',
        'file'    => ':Attribute moet :size kilobytes groot zijn.',
        'string'  => ':Attribute moet :size karakters lang zijn.',
        'array'   => ':Attribute moet :size items bevatten.',
    ],
    'starts_with'          => ':Attribute moet beginnen met één van het volgende: :values',
    'string'               => ':Attribute moet een string zijn.',
    'timezone'             => ':Attribute moet een geldige tijdszone zijn.',
    'unique'               => ':Attribute is al bezet.',
    'uploaded'             => 'Het uploaden van :attribute is mislukt.',
    'url'                  => ':Attribute formaat is ongeldig.',
    'uuid'                 => ':Attribute moet een valide UUID zijn.',

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

            'max' => 'Het totaal aantal punten voor deze toets is :max.
            
            Je kunt geen hoger getal invullen dan het totaal aantal punten voor deze toets.',
        ],
        'ppp' => [
            'max' => 'Het totaal aantal punten voor deze toets is :max.
                            Je kunt geen hoger getal invullen dan het totaal aantal punten voor deze toets.',
        ],

        'wanted_average' => [
            'between' => 'Het gemiddelde cijfer dient tussen de :min en de :max te liggen.'
        ],

        'n_term' => [
            'between' => 'De N-term dient tussen de :min en de :max te liggen.'
        ],

        'pass_mark'              => [
            'between' => 'De Cesuur dient tussen de :min en de :max te liggen.'
        ],
        'is_examcoordinator_for' => [
            'required' => 'kies een van de lesopties waarvoor de examencoördinator examens kan plannen.',
            'in'       => 'kies een van de lesopties waarvoor de examencoördinator examens kan plannen.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'request.abbreviation' => 'afkorting',
        'request.name'         => 'naam',
        'request.introduction' => 'instructie-tekst',
        'request.weight'       => 'gewicht',
    ],

    'school_class_or_guest_accounts_required' => 'Selecteer een klas of Test-direct toestaan.',
    'test name'                               => 'Naam toets',
    'unique_test_name'                        => 'De gekozen naam is al in gebruik. Gebruik een unieke naam',
    'invigilator_required'                    => 'Het is verplicht om een surveillant te kiezen',

    // NIST Password Rules
    'can-not-contain-word'              => 'Het wachtwoord mag het woord \':word\' niet bevatten.',
    'can-not-be-similar-to-word'        => 'Het wachtwoord mag niet vergelijkbaar zijn met het woord \':word\'.',
    'found-in-data-breach'              => 'Het wachtwoord is gevonden in een datalek van een derde partij en mag niet worden gebruikt.',
    'can-not-be-dictionary-word'        => 'Het wachtwoord mag geen woord uit het woordenboek zijn.',
    'can-not-be-repetitive-characters'  => 'Het wachtwoord mag geen herhalende tekens bevatten.',
    'can-not-be-sequential-characters'  => 'Het wachtwoord mag geen opeenvolgende tekens bevatten.',
];
