<?php

namespace Tests\Unit\Rules\NistPassword;

use Faker\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use tcCore\Rules\NistPassword\BreachedPasswords;
use Tests\TestCase;

/**
 * BreachedPasswords class from the NIST package is not up to date and not usable,
 * but Laravel now has a Password rule that can be used to check if a password is not present in a data leak.
 */
class BreachedPasswordsTest extends TestCase
{

    public static function exposedPasswordsProvider()
    {
        return [
            ['test'], ['password'], ['hunter2'],
            ['welkom01'], ['wachtwoord'], ['12345678'],
            ['super123'], ['azertyuiop'], ['pokemon'],
        ];
    }

    /**
     * test whether the Laravel Password uncompromised rule works as expected,
     * by comparing passwords to the HaveIBeenPwned API
     *
     * @dataProvider exposedPasswordsProvider
     * @test
     */
    public function passwordUncompromisedFails($password)
    {
        $validator = validator::make([
                                         'password' => $password
                                     ],
                                     [
                                         'password' => [Password::min(1)->uncompromised()]
                                     ]
        );

        $errorMessage = $validator->errors()->first();

        $this->assertTrue(
            condition: Str::contains($errorMessage, ['datalek', 'data leak']),
            message  : "The first error message should contain the word 'datalek' or 'data leak' in Dutch or English but it is: $errorMessage"
        );

        $this->assertTrue(
            condition: $validator->fails(),
            message  : "The validator should fail because the password is present in a data leak, but it does not."
        );
    }

    public function testPass()
    {
        $validator = validator::make([
                                         'password' => $this->getPasswordUnlikelyToBeExposed()
                                     ],
                                     [
                                         'password' => [Password::min(1)->uncompromised()]
                                     ]
        );

        $this->assertTrue(
            condition: $validator->errors()->isEmpty(),
            message  : "The errors list should be empty but it is not."
        );

        $this->assertTrue(
            condition: $validator->passes(),
            message  : "The validator should pass because the password is not present in a data leak, but it does not."
        );
    }

    private function getPasswordUnlikelyToBeExposed()
    {
        $faker = Factory::create();
        $password = '';
        for ($i = 0; $i < 6; $i++) {
            $password .= $faker->password(14, 20);
            $password .= ' ';
        }
        $password = trim($password);

        return $password;
    }

}
