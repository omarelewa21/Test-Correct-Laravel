<?php

namespace Tests\Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\Factories\FactoryWordList;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\WordList;
use Tests\ScenarioLoader;
use Tests\TestCase;

class CreathlonServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolCreathlon::class;

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('creathlon', CreathlonService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('Creathlon', CreathlonService::getTranslation());
    }

    /** @test */
    public function it_has_a_publish_scope()
    {
        $this->assertEquals('published_creathlon', CreathlonService::getPublishScope());
    }

    /** @test */
    public function it_has_a_not_publish_scope()
    {
        $this->assertEquals('not_published_creathlon', CreathlonService::getNotPublishScope());
    }

    /** @test */
    public function it_has_a_publish_abbreviation()
    {
        $this->assertEquals('PUBLS', CreathlonService::getPublishAbbreviation());
    }

    /** @test */
    public function it_can_tell_if_it_is_available_for_a_user()
    {
        $this->assertFalse(CreathlonService::isAvailableForUser(ScenarioLoader::get('teacherOne')));
    }

    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is allowed to view Creathlon content (only french)
        $teacher->schoolLocation->allow_creathlon = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );
        $this->assertTrue(CreathlonService::isAvailableForUser($teacher));
    }

    /** @test */
    public function when_the_school_location_is_not_allowed_the_subject_the_service_is_not_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Creathlon content
        $teacher->schoolLocation->allow_creathlon = false;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );
        $this->assertFalse(CreathlonService::isAvailableForUser($teacher));
    }


    /** @test */
    public function the_school_location_is_allowed_but_the_teacher_doesnot_teaches_it_the_service_it_not_available()
    {
        //GIVEN that Im logged in
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Formidable content
        $teacher->schoolLocation->allow_creathlon = false;
        $teacher->schoolLocation->save();

        ///GIVEN the teacher has access to the French subject
        $subject = $teacher->subjects()->first();
        $subject->base_subject_id = BaseSubject::FRENCH;
        $subject->save();
        $this->assertTrue(
            $teacher->subjects()->where('base_subject_id', BaseSubject::FRENCH)->exists()
        );

        $this->assertFalse(CreathlonService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertEquals('CREATHLON', CreathlonService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        // that has access to the Dutch subject
        // and the school location is allowed to view Creathlon content
        $teacher = ScenarioLoader::get('teacherOne');
        auth()->login($teacher);
        $teacher->schoolLocation->allow_creathlon = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );

        $this->assertInstanceOf(
            \tcCore\Test::class,
            (new CreathlonService)->itemBankFiltered( forUser: $teacher, filters: [], sorting: [])
                ->where('name', 'test-Creathlon-Frans')
                ->first()
        );
    }

    /** @test */
    public function it_has_a_tab_order()
    {
          $this->assertEquals(500, CreathlonService::$order);
    }
    
    /** @test */
    public function can_show_word_lists_when_all_conditions_are_met()
    {
        $listName = class_basename(CreathlonService::class).' WordList';
        $this->createWordListForSource($listName);
        $teacher = ScenarioLoader::get('teacherOne');
        auth()->login($teacher);

        $teacher->schoolLocation->allow_tm_french = true;
        $teacher->schoolLocation->save();

        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );

        $this->assertInstanceOf(
            WordList::class,
            (new CreathlonService())->wordListFiltered(forUser: $teacher)
                ->where('name', $listName)
                ->first()
        );
    }

    private function createWordListForSource(string $name): WordList
    {
        $subject = ScenarioLoader::get('school_locations')->where('customer_code', CreathlonService::getCustomerCode())
            ->first()
            ->schoolLocationSections
            ->where('demo', false)
            ->first()
            ->section
            ->subjects
            ->where('base_subject_id', BaseSubject::FRENCH)
            ->first();

        return FactoryWordList::createWordList(
            CreathlonService::getSchoolAuthor(),
            ['subject_id' => $subject->getKey(), 'name' => $name]
        );
    }
}
