<?php

namespace Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\Factories\FactoryWordList;
use tcCore\FactoryScenarios\FactoryScenarioSchoolFormidable;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\WordList;
use Tests\ScenarioLoader;
use Tests\TestCase;

class FormidableServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolFormidable::class;

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('formidable', FormidableService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('Formidable!', FormidableService::getTranslation());
    }

    /** @test */
    public function it_has_a_publish_scope()
    {
        $this->assertEquals('published_formidable', FormidableService::getPublishScope());
    }

    /** @test */
    public function it_has_a_not_publish_scope()
    {
        $this->assertEquals('not_published_formidable', FormidableService::getNotPublishScope());
    }

    /** @test */
    public function it_has_a_publish_abbreviation()
    {
        $this->assertEquals('FD', FormidableService::getPublishAbbreviation());
    }

    /** @test */
    public function it_can_tell_if_it_is_available_for_a_user()
    {
        $teacher = ScenarioLoader::get('teacherOne');
        $teacher->schoolLocation->allow_formidable = false;
        $teacher->schoolLocation->save();

        $this->assertFalse(FormidableService::isAvailableForUser(ScenarioLoader::get('teacherOne')));
    }

    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is allowed to view Formidable content (only french)
        $teacher->schoolLocation->allow_formidable = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );
        $this->assertTrue(FormidableService::isAvailableForUser($teacher));
    }

    /** @test */
    public function when_the_school_location_is_not_allowed_the_subject_the_service_is_not_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Formidable! content
        $teacher->schoolLocation->allow_formidable = false;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );
        $this->assertFalse(FormidableService::isAvailableForUser($teacher));
    }


    /** @test */
    public function the_school_location_is_allowed_but_the_teacher_doesnot_teaches_it_the_service_it_not_available()
    {
        //GIVEN that Im logged in
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Formidable content
        $teacher->schoolLocation->allow_formidable = false;
        $teacher->schoolLocation->save();

        ///GIVEN the teacher has access to the French subject
        $subject = $teacher->subjects()->first();
        $subject->base_subject_id = BaseSubject::FRENCH;
        $subject->save();
        $this->assertTrue(
            $teacher->subjects()->where('base_subject_id', BaseSubject::FRENCH)->exists()
        );

        $this->assertFalse(ThiemeMeulenhoffService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertEquals('FORMIDABLE', FormidableService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        // that has access to the Dutch subject
        // and the school location is allowed to view Formidable! content
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        $teacher->schoolLocation->allow_formidable = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );

        $this->assertInstanceOf(
            \tcCore\Test::class,
            (new FormidableService)->itemBankFiltered( forUser: $teacher, filters: [], sorting: [])->where('name', 'test-Formidable-Frans')->first()
        );
    }

    /** @test */
    public function it_has_a_tab_order()
    {
        $this->assertEquals(600, FormidableService::$order);
    }

    /** @test */
    public function can_show_word_lists_when_all_conditions_are_met()
    {
        $listName = class_basename(FormidableService::class).' WordList';
        $this->createWordListForSource($listName);
        $teacher = ScenarioLoader::get('teacherOne');
        auth()->login($teacher);

        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::FRENCH
        );

        $this->assertInstanceOf(
            WordList::class,
            (new FormidableService())->wordListFiltered(forUser: $teacher)
                ->where('name', $listName)
                ->first()
        );
    }

    private function createWordListForSource(string $name): WordList
    {
        $subject = ScenarioLoader::get('school_locations')->where('customer_code', FormidableService::getCustomerCode())
            ->first()
            ->schoolLocationSections
            ->where('demo', false)
            ->first()
            ->section
            ->subjects
            ->where('base_subject_id', BaseSubject::FRENCH)
            ->first();

        return FactoryWordList::createWordList(
            FormidableService::getSchoolAuthor(),
            ['subject_id' => $subject->getKey(), 'name' => $name]
        );
    }
}
