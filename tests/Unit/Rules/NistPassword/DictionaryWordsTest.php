<?php

namespace Tests\Unit\Rules\NistPassword;

use tcCore\Rules\NistPassword\DictionaryWords;
use Tests\TestCase;

class DictionaryWordsTest extends TestCase
{
//    protected function getPackageProviders($app)
//    {
//        return [ServiceProvider::class];
//    }

    public static function dictionaryWordsProvider()
    {
        return [
            //english words
            ['test'],
            ['password'],
            ['cat'],
            ['dog'],
            ['cheese'],
            //dutch words
            ['adembenemend'],
            ['bekend'],
            ['woord'],
            ['koe'],
            ['hond'],
            ['kaas'],
        ];
    }

    public static function nonDictionaryWordsProvider()
    {
        return [
            ['test123'],
            ['passwordz'],
            ['c_a_t'],
            ['d0g'],
            ['ch33s3'],
            //dutch words
            ['ademb3nemend123'],
            ['bek3nd'],
            ['w0ord123'],
            ['k0e123'],
            ['h0nd123'],
            ['k@as123'],
        ];
    }

    /**
     * @dataProvider dictionaryWordsProvider
     */
    public function testFail($password)
    {
        $rule = (new DictionaryWords());
        $this->assertFalse($rule->passes('password', $password));
    }

    /**
     * @dataProvider nonDictionaryWordsProvider
     */
    public function testPass($password)
    {
        $rule = (new DictionaryWords());
        $this->assertTrue($rule->passes('password', $password));
    }

    public function testMessage()
    {
        $rule = (new DictionaryWords());

        $this->assertTrue(array_search($rule->message(), [
                              'The password can not be a dictionary word.',
                              'Het wachtwoord mag geen woord uit het woordenboek zijn.'
                          ]) !== false);

        $this->assertEquals( __('validation.can-not-be-dictionary-word') , $rule->message());
    }
}
