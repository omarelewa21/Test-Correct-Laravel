<?php

namespace Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\Factories\FactoryWordList;
use tcCore\FactoryScenarios\FactoryScenarioSchoolPersonal;
use tcCore\Services\ContentSource\SchoolLocationService;
use tcCore\WordList;
use Tests\ScenarioLoader;
use Tests\TestCase;

class SchoolLocationServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolPersonal::class;

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('school_location', SchoolLocationService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('School', SchoolLocationService::getTranslation());
    }

    /** @test */
    public function it_doesnot_have_a_publish_scope()
    {
        $this->assertNull(SchoolLocationService::getPublishScope());
    }

    /** @test */
    public function it_doesnot_have_a_not_publish_scope()
    {
        $this->assertNull(SchoolLocationService::getNotPublishScope());
    }

    /** @test */
    public function it_doesnot_have_a_publish_abbreviation()
    {
        $this->assertNull(SchoolLocationService::getPublishAbbreviation());
    }

    /** @test */
    public function it_can_tell_if_it_is_available_for_a_user()
    {
        $this->assertTrue(SchoolLocationService::isAvailableForUser(ScenarioLoader::get('dutchTeacher')));
    }

    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('dutchTeacher'));

        $this->assertTrue(SchoolLocationService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertNull( SchoolLocationService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        auth()->login($teacher = ScenarioLoader::get('dutchTeacher'));
        $this->assertInstanceOf(
            \tcCore\Test::class,
            (new SchoolLocationService )->itemBankFiltered(filters:[], sorting:[], forUser:$teacher)->first()
        );
    }

    /** @test */
    public function it_has_a_tab_order()
    {
          $this->assertEquals(200, SchoolLocationService::$order);
    }

    /** @test */
    public function can_show_word_lists_when_all_conditions_are_met()
    {
        $listName = class_basename(SchoolLocationService::class).' WordList';
        $this->createWordListForSource($listName);
        $teacher = ScenarioLoader::get('dutchTeacher');
        auth()->login($teacher);

        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );

        $this->assertInstanceOf(
            WordList::class,
            (new SchoolLocationService())->wordListFiltered(forUser: $teacher)
                ->where('name', $listName)
                ->first()
        );
    }

    private function createWordListForSource(string $name): WordList
    {
        $subject = ScenarioLoader::get('dutchTeacher')->subjects()->first();

        return FactoryWordList::createWordList(
            ScenarioLoader::get('dutchTeacher'),
            ['subject_id' => $subject->getKey(), 'name' => $name]
        );
    }
}
