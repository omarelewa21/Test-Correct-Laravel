<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use tcCore\TemporaryLogin;
use tcCore\User;

class TemporaryLoginTest extends TestCase
{
//    use DatabaseTransactions;

    private $user;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->user = User::find(1483);
    }


    /**
     * @test
     */
    public function can_build_object_from_single_option_and_single_value()
    {
        $option = 'reason';
        $optionValue = 'too slow';

        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);

        $expected = collect([
            'reason' => 'too slow'
        ])->toJson();
        $this->assertJsonStringEqualsJsonString($expected, $result);
    }

    /**
     * @test
     */
    public function can_build_object_from_single_option_and_multiple_values()
    {
        $option = 'reasons';
        $optionValue = ['too slow', 'wrong answers'];

        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);

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
    public function can_build_object_from_multiple_options_and_multiple_values()
    {
        $option = ['reason', 'url'];
        $optionValue = [['too slow'], ['/dashboard']];

        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);

        $expected = collect([
            'reason' => ['too slow'],
            'url' => ['/dashboard']
            ])->toJson();

        $this->assertJsonStringEqualsJsonString($expected, $result);
    }

    /**
     * @test
     */
    public function can_build_object_from_single_option_and_single_multidimensional_value()
    {
        $option = 'reason';
        $optionValue = ['too slow' => '/dashboard'];

        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);

        $expected = collect([
            'reason' => [
                'too slow' => '/dashboard'
            ]
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
        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);
        $this->assertNull($result);

        //Option as array - Multidimensional array as value
        $option = ['reason'];
        $optionValue = [['too slow'], ['/dashboard']];
        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);
        $this->assertNull($result);

        //Multiple options as array - value as string
        $option = ['reason', 'url'];
        $optionValue = 'too slow';
        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);
        $this->assertNull($result);

        //Multiple options as array - value as array
        $option = ['reason', 'url'];
        $optionValue = ['too slow'];
        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);
        $this->assertNull($result);

        //Multidimensional array as option - One value as array
        $option = [['reason'], ['url']];
        $optionValue = ['too slow'];
        $result = TemporaryLogin::buildValidOptionObject($option, $optionValue);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function there_should_not_be_multiple_records_for_one_user()
    {
        TemporaryLogin::createForUser($this->user);
        TemporaryLogin::createWithOptionsForUser('page', '/tests/index', $this->user);

        $this->assertEquals(1, $this->user->temporaryLogin()->count());
    }

    /**
     * @test
     */
    public function should_create_record_without_options_if_options_are_incorrect_format()
    {
        $option = [['reason'], ['url']];
        $optionValue = ['too slow'];

        TemporaryLogin::createWithOptionsForUser($option, $optionValue, $this->user);

        $temporaryLogin = $this->user->temporaryLogin;

        $this->assertNull($temporaryLogin->options);
    }
}
