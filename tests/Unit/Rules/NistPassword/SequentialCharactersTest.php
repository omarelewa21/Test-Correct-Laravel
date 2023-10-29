<?php

namespace Tests\Unit\Rules\NistPassword;

use tcCore\Rules\NistPassword\SequentialCharacters;
use Tests\TestCase;

class SequentialCharactersTest extends TestCase
{
    public function sequentialCharactersProvider()
    {
        return [
            ['123'],
            ['12345'],
            ['abcdef'],
            ['ghij'],
            ['321'],
        ];
    }

    public function nonSequentialCharactersProvider()
    {
        return [
            ['aa'],
            ['332211'],
            ['teeth'],
            ['passwordz'],
            ['cheese'],
            ['l337'],
        ];
    }

    /**
     * @dataProvider sequentialCharactersProvider
     */
    public function testFail($password)
    {
        $rule = (new SequentialCharacters());
        $this->assertFalse($rule->passes('password', $password));
    }

    /**
     * @dataProvider nonSequentialCharactersProvider
     */
    public function testPass($password)
    {
        $rule = (new SequentialCharacters());
        $this->assertTrue($rule->passes('password', $password));
    }

    public function testMessage()
    {
        $rule = (new SequentialCharacters());

        $this->assertTrue(array_search($rule->message(), [
                              'The password can not be sequential characters.',
                              'Het wachtwoord mag geen opeenvolgende tekens bevatten.',
                          ]) !== false);

        $this->assertEquals(__('validation.can-not-be-sequential-characters'), $rule->message());
    }
}
