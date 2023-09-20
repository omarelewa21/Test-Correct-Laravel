<?php

namespace Tests\Unit\Rules\NistPassword;

use Faker\Factory;
use tcCore\Rules\NistPassword\BreachedPasswords;
use Tests\TestCase;

class BreachedPasswordsTest extends TestCase
{

    public static function exposedPasswordsProvider()
    {
        return [
            ['test'],
            ['password'],
            ['hunter2'],
        ];
    }

    /**
     * @dataProvider exposedPasswordsProvider
     */
    public function testFail($password)
    {
        $rule = (new BreachedPasswords());
        $this->assertFalse($rule->passes('password', $password));
    }

    public function testPass()
    {
        $password = $this->getPasswordUnlikelyToBeExposed();

        $rule = (new BreachedPasswords());
        $this->assertTrue($rule->passes('password', $password));
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

    public function testMessage()
    {
        $rule = (new BreachedPasswords());
        $this->assertEquals('The password was found in a third party data breach, and can not be used.', $rule->message());
    }
}
