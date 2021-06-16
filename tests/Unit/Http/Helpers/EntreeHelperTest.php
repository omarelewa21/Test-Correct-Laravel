<?php


namespace Tests\Unit\Http\Helpers;


use tcCore\Http\Helpers\EntreeHelper;
use tcCore\SchoolLocation;
use Tests\TestCase;

class EntreeHelperTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_return_true_when_a_valid_brin_zes_is_provided()
    {
        // MagisterTestSchool;
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $this->assertInstanceOf(SchoolLocation::class, $location);

        $this->assertTrue(EntreeHelper::redirectIfBrinUnknown('99DE00'));
    }

    /**
     * @test
     */
    public function it_should_redirect_when_a_invalid_brin_zes_is_provided()
    {
        // MagisterTestSchool;
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '01')->first();
        $this->assertNull( $location);

        $this->assertEquals(
            route('auth.login', ['tab' => 'login', 'message_brin' => 'brin_not_found']),
            EntreeHelper::redirectIfBrinUnknown('99DE01')
        );
    }
    /**
     * @test
     */
    public function it_should_redirect_when_no_brin_zes_is_provided()
    {
        $this->assertEquals(
            route('auth.login', ['tab' => 'login', 'message_brin' => 'brin_not_found']),
            EntreeHelper::redirectIfBrinUnknown()
        );
    }


}
