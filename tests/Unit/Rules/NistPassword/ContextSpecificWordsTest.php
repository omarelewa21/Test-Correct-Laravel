<?php

namespace Tests\Unit\Rules\NistPassword;

use tcCore\Rules\NistPassword\ContextSpecificWords;
use Tests\TestCase;

class ContextSpecificWordsTest extends TestCase
{
    private static string $username = "a.student@test-correct.nl";

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    public static function contextSpecificWordsProvider()
    {
        return [
            ['a.student'],
            ['test-correct'],
            [self::$username],
            [strtoupper(self::$username)],
            ['123' . self::$username . '111'],
            ['123' . strtoupper(self::$username) . '111'],
            ['cat' . self::$username],
            ['dog' . self::$username . '!!!'],
            ['che' . self::$username . 'ese'],
            ['test' . self::$username . 'ese'],
            ['test' . self::$username . 'welcome'],
        ];
    }

    public static function nonContextSpecificWordsProvider()
    {
        return [
            ['b.student'],
            ['student2'],
            ['tes123'],
            ['passwordz'],
            ['c_a_t'],
            ['d0g'],
            ['ch33s3'],
            ['woorden'],
            ['appel'],
            ['peer'],
            ['banaan'],
            ['koe123'],
            ['h0nd123'],
            ['k@as123'],
        ];
    }

    /**
     * @dataProvider contextSpecificWordsProvider
     */
    public function testFail($password)
    {
        $rule = (new ContextSpecificWords(static::$username));
        $this->assertFalse($rule->passes('password', $password));
    }

    /**
     * @dataProvider nonContextSpecificWordsProvider
     */
    public function testPass($password)
    {
        $rule = (new ContextSpecificWords(self::$username));
        $this->assertTrue($rule->passes('password', $password));
    }

    public function testMessage()
    {
        $rule = (new ContextSpecificWords(self::$username));

        $this->assertTrue(
            array_search(
                $rule->message(), [
                    "The password can not contain the word ''.",
                    "Het wachtwoord mag het woord '' niet bevatten."
                ]) !== false);

        $this->assertEquals(__('validation.can-not-contain-word', ['word' => '']), $rule->message());
    }

    public function testShortUsernamesAreExcluded()
    {
        $rule = (new ContextSpecificWords('ca'));
        $this->assertTrue($rule->passes('password', 'cat'));
    }

}
