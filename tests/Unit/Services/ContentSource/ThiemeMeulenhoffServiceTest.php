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

    }

    /** @test */
    public function it_has_a_name()
    {
       $this->assertEquals('thieme_meulenhoff', ThiemeMeulenhoffService::getName());
    }

    /** @test */
    public function it_has_a_translation()
    {
        $this->assertEquals('Thieme Meulenhoff', ThiemeMeulenhoffService::getTranslation());
    }

    /** @test */
    public function it_has_a_publish_scope()
    {
        $this->assertEquals('published_thieme_meulenhoff', ThiemeMeulenhoffService::getPublishScope());
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
    public function it_can_tell_if_it_is_allowed_for_a_user()
    {
        $teacher = ScenarioLoader::get('teacherOne');
        $this->assertEquals(
            $teacher->subjects()->first()->base_subject_id,
            BaseSubject::DUTCH
        );
       $teacher->schoolLocation->allow_tm_dutch = true;
       $teacher->schoolLocation->save();
       $this->assertFalse(ThiemeMeulenhoffService::isAvailableForUser(ScenarioLoader::get('teacherOne')));
    }
}