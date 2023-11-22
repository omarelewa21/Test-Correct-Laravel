<?php

namespace Tests\Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\FactoryScenarios\FactoryScenarioSchoolOlympiade;
use tcCore\Services\ContentSource\OlympiadeService;
use Tests\ScenarioLoader;
use Tests\TestCase;

class OlympiadeServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolOlympiade::class;

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('olympiade', OlympiadeService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('Olympiade', OlympiadeService::getTranslation());
    }

    /** @test */
    public function it_has_a_publish_scope()
    {
        $this->assertEquals('published_olympiade', OlympiadeService::getPublishScope());
    }

    /** @test */
    public function it_has_a_not_publish_scope()
    {
        $this->assertEquals('not_published_olympiade', OlympiadeService::getNotPublishScope());
    }

    /** @test */
    public function it_has_a_publish_abbreviation()
    {
        $this->assertEquals('SBON', OlympiadeService::getPublishAbbreviation());
    }

    /** @test */
    public function it_can_tell_if_it_is_available_for_a_user()
    {
        $this->assertFalse(OlympiadeService::isAvailableForUser(ScenarioLoader::get('teacherOne')));
    }

    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is allowed to view Olympiade content (only french)
        $teacher->schoolLocation->allow_olympiade = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            BaseSubject::DUTCH,
            $teacher->subjects()->first()->base_subject_id,
        );
        $this->assertTrue(OlympiadeService::isAvailableForUser($teacher));
    }

    /** @test */
    public function when_the_school_location_is_not_allowed_the_subject_the_service_is_not_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Olympiade content
        $teacher->schoolLocation->allow_olympiade = false;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the French subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );
        $this->assertFalse(OlympiadeService::isAvailableForUser($teacher));
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

        $this->assertFalse(OlympiadeService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertEquals('SBON', OlympiadeService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        // that has access to the Dutch subject
        // and the school location is allowed to view Olympiade content
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        $teacher->schoolLocation->allow_olympiade = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the Dutch subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );

        $this->assertInstanceOf(
            \tcCore\Test::class,
            (new OlympiadeService)->itemBankFiltered( forUser: $teacher, filters: [], sorting: [])->where('name', 'test-Olympiade-Nederlands')->first()
        );
    }

    /** @test */
    public function it_has_a_tab_order()
    {
        $this->assertEquals(600, OlympiadeService::$order);
    }
}
