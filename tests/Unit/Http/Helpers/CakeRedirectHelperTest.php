<?php

namespace Tests\Unit\Http\Helpers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\SchoolLocation;
use Tests\ScenarioLoader;
use Tests\TestCase;

class CakeRedirectHelperTest extends TestCase
{
    use DatabaseTransactions;
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    /** @test */
    public function can_get_routename_by_url_in_one_dimensional_context()
    {
        $url = '/questions/index';
        $routeName = 'tests.question_bank';
        $retrievedRouteName = CakeRedirectHelper::getRouteNameByUrl($url);

        $this->assertEquals($routeName, $retrievedRouteName);
    }

    /** @test */
    public function can_return_null_when_wrong_url_is_requested()
    {
        $url = '/questions/aardbei';
        $retrievedRouteName = CakeRedirectHelper::getRouteNameByUrl($url);

        $this->assertNull($retrievedRouteName);
    }

    /** @test */
    public function can_get_routename_by_url_in_multi_dimensional_context()
    {
        $url = sprintf("/school_locations/view/%s", SchoolLocation::first()->uuid);
        $routeName = 'school_location.view';
        $retrievedRouteName = CakeRedirectHelper::getRouteNameByUrl($url, SchoolLocation::first()->uuid);

        $this->assertEquals($routeName, $retrievedRouteName);
    }

    /** @test */
    public function can_redirect_with_retrieved_route_name_by_url()
    {
        $url = sprintf("/school_locations/view/%s", SchoolLocation::first()->uuid);
        $routeName = 'school_location.view';
        $retrievedRouteName = CakeRedirectHelper::getRouteNameByUrl($url, SchoolLocation::first()->uuid);

        $this->assertEquals($routeName, $retrievedRouteName);

        $this->actingAs(ScenarioLoader::get('user'));
        $result = CakeRedirectHelper::redirectToCake($retrievedRouteName);

        $this->assertEquals(302, $result->getStatusCode());
    }

    /** @test */
    public function can_get_routename_by_url_in_one_dimensional_context_when_url_contains_uuid()
    {
        $url = '/test_takes/normalization/5b1f8dc5-fcdf-11ea-92d9-5616569c777a';
        $routeName = 'taken.normalize';
        $retrievedRouteName = CakeRedirectHelper::getRouteNameByUrl($url);

        $this->assertEquals($routeName, $retrievedRouteName);
    }
}
