<?php

namespace tcCore\Rules;

use tcCore\Rules\NistPassword\BreachedPasswords;
use tcCore\Rules\NistPassword\ContextSpecificWords;
use tcCore\Rules\NistPassword\DerivativesOfContextSpecificWords;
use tcCore\Rules\NistPassword\DictionaryWords;
use tcCore\Rules\NistPassword\RepetitiveCharacters;
use tcCore\Rules\NistPassword\SequentialCharacters;
use tcCore\User;

abstract class NistPasswordRules {
    public static function register($username, $requireConfirmation = true)
    {
        $rules = [
            'required',
            'string',
            sprintf("min:%s", User::MIN_PASSWORD_LENGTH),
        ];

        if ($requireConfirmation) {
            $rules[] = 'confirmed';
        }

        return array_merge($rules, [
            new SequentialCharacters(),
            new RepetitiveCharacters(),
            new DictionaryWords(),
            new ContextSpecificWords($username),
            new DerivativesOfContextSpecificWords($username),
            new BreachedPasswords(),
        ]);
    }

    public static function changePassword($username, $oldPassword = null)
    {
        $rules = self::register($username);

        if ($oldPassword) {
            $rules = array_merge($rules, [
                'different:'.$oldPassword,
            ]);
        }

        return $rules;
    }

    public static function optionallyChangePassword($username, $oldPassword = null)
    {
        $rules = self::changePassword($username, $oldPassword);

        $rules = array_merge($rules, [
            'nullable',
        ]);

        foreach ($rules as $key => $rule) {
            if (is_string($rule) && $rule === 'required') {
                unset($rules[$key]);
            }
        }

        return $rules;
    }

    public static function login()
    {
        return [
            'required',
            'string',
        ];
    }
}
