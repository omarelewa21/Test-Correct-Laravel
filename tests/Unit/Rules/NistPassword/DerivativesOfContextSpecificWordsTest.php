<?php

namespace Tests\Unit\Rules\NistPassword;

use tcCore\Rules\NistPassword\DerivativesOfContextSpecificWords;
use Tests\TestCase;

class DerivativesOfContextSpecificWordsTest extends TestCase
{
    private static $username = "docent@test-correct.nl";

    public function contextSpecificWordsProvider()
    {
        return [
            [self::$username],
            [strtoupper(self::$username)],
            [substr(self::$username, 0, -2)],
            [strtoupper(substr(self::$username, 0, -2))],
            [substr(self::$username, 2)],
            [substr(self::$username, 1, -1)],
        ];
    }

    public function nonContextSpecificWordsProvider()
    {
        return [
            ['test123'],
            ['passwordz'],
            ['c_a_t'],
            ['d0g'],
            ['ch33s3'],
        ];
    }

    /**
     * @dataProvider contextSpecificWordsProvider
     */
    public function testFail($password)
    {
        $rule = (new DerivativesOfContextSpecificWords(self::$username));
        $this->assertFalse($rule->passes('password', $password));
    }

    /**
     * @dataProvider nonContextSpecificWordsProvider
     */
    public function testPass($password)
    {
        $rule = (new DerivativesOfContextSpecificWords(self::$username));
        $this->assertTrue($rule->passes('password', $password));
    }

    public function testMessage()
    {
        $rule = (new DerivativesOfContextSpecificWords(self::$username));

        $this->assertTrue(array_search($rule->message(), [
                              'The password can not be similar to the word \'\'.',
                              'Het wachtwoord mag niet vergelijkbaar zijn met het woord \'\'.',
                          ]) !== false);

        $this->assertEquals(__('validation.can-not-be-similar-to-word', ['word' => '']), $rule->message());
    }
}
