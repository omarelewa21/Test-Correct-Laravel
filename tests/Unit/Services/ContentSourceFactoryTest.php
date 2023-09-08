<?php

namespace Unit\Services;

use tcCore\FactoryScenarios\FactoryScenarioSchoolNationalItemBank;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\ContentSourceService;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\PersonalService;
use tcCore\Services\ContentSource\SchoolLocationService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use tcCore\Services\ContentSourceFactory;
use tcCore\Test;
use Tests\ScenarioLoader;
use Tests\TestCase;

class ContentSourceFactoryTest extends TestCase
{
   protected $loadScenario =  FactoryScenarioSchoolNationalItemBank::class;
    /**
     * @dataProvider scopeDataProvider
     */
    public function testMakeWithTestBasedOnScope($scope, $expectedClass)
    {
        $mock = \Mockery::mock(Test::class);
        $mock->shouldReceive('getAttribute')->with('scope')->andReturn($scope);

        $this->assertInstanceOf($expectedClass, ContentSourceFactory::makeWithTestBasedOnScope($mock));
    }

    public static function scopeDataProvider()
    {
        return [
            ['ltd', NationalItemBankService::class],
            ['published_formidable', FormidableService::class],
            ['published_thieme_meulenhoff', ThiemeMeulenhoffService::class],
            ['published_creathlon', CreathlonService::class],
            ['published_olympiade', OlympiadeService::class],
            // Add more test cases as needed
        ];
    }

    /**
     * @test
     */
    public function testGetPublishableAuthorByCustomerCode()
    {
        ScenarioLoader::load(FactoryScenarioSchoolNationalItemBank::class);

        $author = ContentSourceFactory::getPublishableAuthorByCustomerCode(
            config('custom.national_item_bank_school_customercode')
        );

        $this->assertEquals(
            config('custom.national_item_bank_school_author'),
            $author->username
        );
    }

    /**
     * @test
     */
    public function testGetPublishableAuthorByNotMappedCustomerCodeShouldReturnNull()
    {
        $this->assertInstanceOf(
            SchoolLocation::class,
            SchoolLocation::where('customer_code', 'ABC')->first()
        );

        $this->assertNull(ContentSourceFactory::getPublishableAuthorByCustomerCode('ABC'));
    }

    /**
     * @test
     */
    public function testGetPublishableAuthorByForNoneExistingCustomerCodeShouldReturnNull()
    {
        $this->assertNull(SchoolLocation::where('customer_code', 'none_existing')->first());

        $this->assertNull(ContentSourceFactory::getPublishableAuthorByCustomerCode('none_existing'));
    }
    /**
     * @dataProvider tabProvider
     */
    public function testMakeWithTab($tab, $expectedInstance)
    {
        $service = ContentSourceFactory::makeWithTab($tab);
        $this->assertInstanceOf($expectedInstance, $service);
    }

    public function tabProvider()
    {
        return [
            ['school_location', SchoolLocationService::class],
            ['national', NationalItemBankService::class],
            ['umbrella', UmbrellaOrganizationService::class],
            ['formidable', FormidableService::class],
            ['creathlon', CreathlonService::class],
            ['olympiade', OlympiadeService::class],
            ['thieme_meulenhoff', ThiemeMeulenhoffService::class],
            ['personal', PersonalService::class],
            ['unknown_tab', PersonalService::class], // Testing the default case
        ];
    }

    /**
     * @dataProvider externalOnlyTabProvider
     */
    public function testMakeWithTabExternalOnly($tab, $expectedInstance)
    {
        $service = ContentSourceFactory::makeWithTabExternalOnly($tab);

        if ($expectedInstance === null) {
            $this->assertNull($service);
        } else {
            $this->assertInstanceOf($expectedInstance, $service);
        }
    }

    public function externalOnlyTabProvider()
    {
        return [
            ['umbrella', UmbrellaOrganizationService::class],
            ['formidable', FormidableService::class],
            ['creathlon', CreathlonService::class],
            ['olympiade', OlympiadeService::class],
            ['thieme_meulenhoff', ThiemeMeulenhoffService::class],
            ['unknown_tab', null], // Testing the default case with null return
        ];
    }



}
