<?php

namespace Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\FactoryScenarios\FactoryScenarioSchoolPersonal;
use tcCore\Services\ContentSource\PersonalService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use Tests\ScenarioLoader;
use Tests\TestCase;

class PersonalServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolPersonal::class;

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('personal', PersonalService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('Persoonlijk', PersonalService::getTranslation());
    }

    /** @test */
    public function it_doesnot_have_a_publish_scope()
    {
        $this->assertNull(PersonalService::getPublishScope());
    }

    /** @test */
    public function it_doesnot_have_a_not_publish_scope()
    {
        $this->assertNull(PersonalService::getNotPublishScope());
    }

    /** @test */
    public function it_doesnot_have_a_publish_abbreviation()
    {
        $this->assertNull(PersonalService::getPublishAbbreviation());
    }

    /** @test */
    public function it_can_tell_if_it_is_available_for_a_user()
    {
        $this->assertTrue(PersonalService::isAvailableForUser(ScenarioLoader::get('dutchTeacher')));
    }

    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('dutchTeacher'));

        $this->assertTrue(PersonalService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertNull( PersonalService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        auth()->login($teacher = ScenarioLoader::get('dutchTeacher'));
        $this->assertInstanceOf(
            \tcCore\Test::class,
            (new PersonalService )->itemBankFiltered(filters:[], sorting:[], forUser:$teacher)->first()
        );
    }

    /** @test */
    public function it_has_a_tab_order()
    {
          $this->assertEquals(100, PersonalService::$order);
    }
}
