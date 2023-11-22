<?php

namespace Unit\Services\ContentSource;


use tcCore\BaseSubject;
use tcCore\FactoryScenarios\FactoryScenarioSchoolThiemeMeulenhoff;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use Tests\ScenarioLoader;
use Tests\TestCase;

class ThiemeMeulenhoffServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolThiemeMeulenhoff::class;


    protected function setUp(): void
    {
//        static::$skipRefresh = true;

        parent::setUp();
        ActingAsHelper::getInstance()->setUser(ScenarioLoader::get('teacherOne'));
        auth()->login(ScenarioLoader::get('teacherOne'));

    }

    /** @test */
    public function it_has_a_name()
    {
        $this->assertEquals('thieme_meulenhoff', ThiemeMeulenhoffService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('ThiemeMeulenhoff', ThiemeMeulenhoffService::getTranslation());
    }

    /** @test */
    public function it_has_a_publish_scope()
    {
        $this->assertEquals('published_thieme_meulenhoff', ThiemeMeulenhoffService::getPublishScope());
    }

    /** @test */
    public function it_has_a_not_publish_scope()
    {
        $this->assertEquals('not_published_thieme_meulenhoff', ThiemeMeulenhoffService::getNotPublishScope());

    }

    /** @test */
    public function it_has_a_publish_abbreviation()
    {
        $this->assertEquals('TM', ThiemeMeulenhoffService::getPublishAbbreviation());
    }

    /** @test */
    public function it_can_return_all_feature_settings()
    {
        $this->assertEquals(collect([
            'allow_tm_biology',
            'allow_tm_geography',
            'allow_tm_dutch',
            'allow_tm_english',
            'allow_tm_french',
        ]), ThiemeMeulenhoffService::getAllFeatureSettings());
    }

    /** @test */
    public function it_can_tell_if_it_is_available_for_a_user()
    {
        $this->assertFalse(ThiemeMeulenhoffService::isAvailableForUser(ScenarioLoader::get('teacherOne')));
    }

    /** @test */
    public function when_all_conditions_are_met_the_service_is_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is allowed to view Thieme Meulenhoff content for dutch
        $teacher->schoolLocation->allow_tm_dutch = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the Dutch subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );
        $this->assertTrue(ThiemeMeulenhoffService::isAvailableForUser($teacher));
    }

    /** @test */
    public function when_the_school_location_is_not_allowed_the_subject_the_service_is_not_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Thieme Meulenhoff content for dutch
        $teacher->schoolLocation->allow_tm_dutch = false;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the Dutch subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );
        $this->assertFalse(ThiemeMeulenhoffService::isAvailableForUser($teacher));
    }

    /** @test */
    public function when_the_school_location_has_another_subject_allowed_the_service_is_not_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Thieme Meulenhoff content for dutch
        $teacher->schoolLocation->allow_tm_dutch = false;
        $teacher->schoolLocation->allow_tm_french = true;

        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the Dutch subject
        $this->assertTrue(
            $teacher->subjects()->where('base_subject_id', BaseSubject::DUTCH)->exists()
        );
        $this->assertFalse(ThiemeMeulenhoffService::isAvailableForUser($teacher));
    }

    /** @test */
    public function the_school_location_is_allowed_the_subject_but_the_teacher_doesnot_teach_the_service_it_not_available()
    {
        //GIVEN that Im logged
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        //GIVEN the school of teacherOne is NOT allowed to view Thieme Meulenhoff content for dutch
        $teacher->schoolLocation->allow_tm_dutch = false;
        $teacher->schoolLocation->allow_tm_french = true;
        $teacher->schoolLocation->save();

        ///GIVEN the teacher has access to the French subject
        $subject = $teacher->subjects()->first();
        $subject->base_subject_id = BaseSubject::FRENCH;
        $subject->save();
        $this->assertTrue(
            $teacher->subjects()->where('base_subject_id', BaseSubject::FRENCH)->exists()
        );
        //GIVEN the teacher does not teach the Dutch subject
        $this->assertFalse(
            $teacher->subjects()->where('base_subject_id', BaseSubject::DUTCH)->exists()
        );

        $this->assertFalse(ThiemeMeulenhoffService::isAvailableForUser($teacher));
    }

    /** @test */
    public function it_can_get_the_customer_code()
    {
        $this->assertEquals('THIEMEMEULENHOFF', ThiemeMeulenhoffService::getCustomerCode());
    }

    /** @test */
    public function it_can_show_results_when_querying_the_item_bank()
    {
        //GIVEN that Im logged in as a teacher
        // that has access to the Dutch subject
        // and the school location is allowed to view Thieme Meulenhoff content for dutch
        auth()->login($teacher = ScenarioLoader::get('teacherOne'));
        $teacher->schoolLocation->allow_tm_dutch = true;
        $teacher->schoolLocation->save();
        ///GIVEN the teacher has access to the Dutch subject
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );

        $this->assertInstanceOf(
            \tcCore\Test::class,
            (new ThiemeMeulenhoffService)->itemBankFiltered( forUser: $teacher, filters: [], sorting: [])->where('name', 'test-ThiemeMeulenhoff-Nederlands')->first()
        );
    }

    /** @test */
    public function it_has_a_tab_order()
    {
          $this->assertEquals(700, ThiemeMeulenhoffService::$order);
    }
}
