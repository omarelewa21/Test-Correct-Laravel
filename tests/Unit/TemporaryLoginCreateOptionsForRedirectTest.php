<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use tcCore\TemporaryLogin;

class TemporaryLoginCreateOptionsForRedirectTest extends TestCase
{


    /**
     * @test
     */
    public function create_json_object_from_single_option_and_single_value()
    {
        $option = 'reason';
        $optionValue = 'too slow';

        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);

        $expected = collect([
            'reason' => 'too slow'
        ])->toJson();
        $this->assertJsonStringEqualsJsonString($expected, $result);
    }

    /**
     * @test
     */
    public function create_json_object_from_single_option_and_multiple_values()
    {
        $option = 'reasons';
        $optionValue = ['too slow', 'wrong answers'];

        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);

        $expected = collect([
            'reasons' => [
                'too slow',
                'wrong answers'
            ]
        ])->toJson();

        $this->assertJsonStringEqualsJsonString($expected, $result);
    }

    /**
     * @test
     */
    public function create_json_object_from_multiple_options_and_multiple_values()
    {
        $option = ['reason', 'url'];
        $optionValue = [['too slow'], ['/dashboard']];

        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);

        $expected = collect([
            'reason' => ['too slow'],
            'url' => ['/dashboard']
            ])->toJson();

        $this->assertJsonStringEqualsJsonString($expected, $result);
    }

    /**
     * @test
     */
    public function return_null_if_option_amount_and_value_amount_do_not_match()
    {
        //Option as string - Multidimensional array as value
        $option = 'reason';
        $optionValue = [['too slow'], ['/dashboard']];
        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);
        $this->assertNull($result);

        //Option as array - Multidimensional array as value
        $option = ['reason'];
        $optionValue = [['too slow'], ['/dashboard']];
        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);
        $this->assertNull($result);

        //Multiple options as array - value as string
        $option = ['reason', 'url'];
        $optionValue = 'too slow';
        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);
        $this->assertNull($result);

        //Multiple options as array - value as array
        $option = ['reason', 'url'];
        $optionValue = ['too slow'];
        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);
        $this->assertNull($result);

        //Multidimensional array as option - One value as array
        $option = [['reason'], ['url']];
        $optionValue = ['too slow'];
        $result = TemporaryLogin::createOptionsForRedirect($option, $optionValue);
        $this->assertNull($result);
    }
}
