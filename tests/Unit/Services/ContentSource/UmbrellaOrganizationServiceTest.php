<?php

namespace Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\Factories\FactoryWordList;
use tcCore\FactoryScenarios\FactoryScenarioSchoolUmbrellaOrganization;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use tcCore\WordList;
use Tests\ScenarioLoader;
use Tests\TestCase;

class UmbrellaOrganizationServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolUmbrellaOrganization::class;

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('umbrella', UmbrellaOrganizationService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('Scholengemeenschap', UmbrellaOrganizationService::getTranslation());
    }

    /** @test */
    public function it_doesnot_have_a_publish_scope()
    {
        $this->assertNull(UmbrellaOrganizationService::getPublishScope());
    }

    /** @test */
    public function it_doesnot_have_a_not_publish_scope()
    {
        $this->assertNull(UmbrellaOrganizationService::getNotPublishScope());
    }

    /** @test */
    public function it_doesnot_have_a_publish_abbreviation()
    {
        $this->assertNull(UmbrellaOrganizationService::getPublishAbbreviation());
    }


    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));

        $this->assertTrue(UmbrellaOrganizationService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertNull( UmbrellaOrganizationService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        $this->assertInstanceOf(
            \tcCore\Test::class,
            $test = (new UmbrellaOrganizationService)->itemBankFiltered(auth()->user(), [], [])->first()
        );

        $this->assertTrue(
            $teacher->schoolLocation->isNot($test->owner)
        );
    }

    /** @test */
    public function when_no_content_is_shared_from_another_school_location_you_wont_see_the_service_is_not_available()
    {
        auth()->login($teacher = ScenarioLoader::get('teacherUmbrella'));
        $this->assertFalse(UmbrellaOrganizationService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_has_a_tab_order()
    {
          $this->assertEquals(300, UmbrellaOrganizationService::$order);
    }


    /** @test */
    public function can_show_word_lists_when_all_conditions_are_met()
    {
        $listName = class_basename(UmbrellaOrganizationService::class).' WordList';
        $this->createWordListForSource($listName);
        $teacher = ScenarioLoader::get('teacherOne');
        auth()->login($teacher);

        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );

        $this->assertInstanceOf(
            WordList::class,
            (new UmbrellaOrganizationService())->wordListFiltered(forUser: $teacher)
                ->where('name', $listName)
                ->first()
        );
    }

    private function createWordListForSource(string $name): WordList
    {
        $subject = ScenarioLoader::get('teacherUmbrella')
            ->subjects()
            ->where('base_subject_id', BaseSubject::DUTCH)
            ->first();

        return FactoryWordList::createWordList(
            ScenarioLoader::get('teacherUmbrella'),
            ['subject_id' => $subject->getKey(), 'name' => $name]
        );
    }
}
