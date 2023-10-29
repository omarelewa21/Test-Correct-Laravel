<?php

namespace Tests\Unit\Rules\NistPassword;

use tcCore\Rules\NistPassword\RepetitiveCharacters;
use Tests\TestCase;

class RepetitiveCharactersTest extends TestCase
{
    public function repetitiveCharactersProvider()
    {
        return [
            ['aaa'],
            ['1111'],
            ['aaaaaaaa'],
            ['eeeeee'],
            ['33333'],
        ];
    }

    public function nonRepetitiveCharactersProvider()
    {
        return [
            ['aaaaab'],
            ['112233'],
            ['teeth'],
            ['passwordz'],
            ['cheese'],
            ['l337'],
        ];
    }

    /**
     * @dataProvider repetitiveCharactersProvider
     */
    public function testFail($password)
    {
        $rule = (new RepetitiveCharacters());
        $this->assertFalse($rule->passes('password', $password));
    }

    /**
     * @dataProvider nonRepetitiveCharactersProvider
     */
    public function testPass($password)
    {
        $rule = (new RepetitiveCharacters());
        $this->assertTrue($rule->passes('password', $password));
    }

    public function testMessage()
    {
        $rule = (new RepetitiveCharacters());

        $this->assertTrue(array_search($rule->message(), [
                              'The password can not be repetitive characters.',
                              'Het wachtwoord mag geen herhalende tekens bevatten.',
                          ]) !== false);

        $this->assertEquals(__('validation.can-not-be-repetitive-characters'), $rule->message());
    }
}
