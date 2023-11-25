<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FeatureSetting;
use tcCore\Http\Enums\TestPackages;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class SchoolLocationFeatureSettingsTest extends TestCase
{
    use DatabaseTransactions;
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    /**
     * @test
     * @dataProvider validTestPackageValues
     */
    public function setTestPackageByValidValues($validSetValue, $expectedValue)
    {
        $schoolLocation = $this->getSchoolLocationAndClearFeatureSetting();

        $schoolLocation->testPackage = $validSetValue;

        $this->assertEquals($expectedValue, $schoolLocation->testPackage);
        $this->assertEquals($expectedValue->value, $schoolLocation->testPackage->value);
    }

    /**
     * @test
     * @dataProvider invalidTestPackageValues
     */
    public function settingTestPackageWithInvalidValuesThrowsValueError($invalidSetValue, $error)
    {
        $schoolLocation = $this->getSchoolLocationAndClearFeatureSetting();

        $this->expectException($error);

        $schoolLocation->testPackage = $invalidSetValue;
    }

    /** @test */
    public function testPackageReturnsEnumNoneByDefault()
    {
        $schoolLocation = $this->getSchoolLocationAndClearFeatureSetting();
        $schoolLocationTestPackage = FeatureSetting::where('settingable_type', '=', $schoolLocation->getMorphClass())
            ->where('settingable_id', '=', $schoolLocation->getKey())
            ->where('title', '=', 'test_package')
            ->first();

        $this->assertNull($schoolLocationTestPackage);

        $defaultTestPackage = $schoolLocation->testPackage;

        $this->assertEquals(TestPackages::None, $defaultTestPackage);

    }

    /** @test */
    public function getAvailableValuesFromEnumObject()
    {
        $availableOptions = TestPackages::values();

        $this->assertEquals([
            'none',
            'basic',
            'pro',
        ], $availableOptions);
    }

    /**
     * @test
     * @dataProvider booleanFeatureSettings
     */
    public function setAndGetBooleanFeatureSettings($booleanFeatureSetting)
    {
        $schoolLocation = $this->getSchoolLocationAndClearFeatureSetting();
        $this->assertFalse($schoolLocation->{$booleanFeatureSetting});

        $schoolLocation->{$booleanFeatureSetting} = true;

        $this->assertTrue($schoolLocation->{$booleanFeatureSetting});
    }

    /**
     * @test
     * @dataProvider booleanFeatureSettings
     */
    public function canGetIssetFeatureSettings($booleanFeatureSetting)
    {
        $schoolLocation = $this->getSchoolLocationAndClearFeatureSetting();
        $this->assertFalse($schoolLocation->{$booleanFeatureSetting});
        $schoolLocation->{$booleanFeatureSetting} = true;
        $this->assertTrue($schoolLocation->{$booleanFeatureSetting});

    }


    /*
     * Dataproviders
     */
    public function validTestPackageValues()
    {
        return [
            'set by string 1'                  => ['none', TestPackages::None],
            'set by string 2'                  => ['basic', TestPackages::Basic],
            'set by string 3'                  => ['pro', TestPackages::Pro],
            'set by string different casing 1' => ['BaSiC', TestPackages::Basic],
            'set by string different casing 2' => ['BASIC', TestPackages::Basic],
            'set by Enum object 1'             => [TestPackages::Basic, TestPackages::Basic],
            'set by Enum object 2'             => [TestPackages::Pro, TestPackages::Pro],
            'set by Enum object 3'             => [TestPackages::None, TestPackages::None],
            'delete with false'                => [false, TestPackages::None],
        ];
    }

    public function invalidTestPackageValues()
    {
        return [
            'invalid value'        => ['invalid', \ValueError::class],
            'true boolean'         => [true, \ValueError::class],
            'false string boolean' => ['false', \ValueError::class],
            'integer 0'            => [0, \ValueError::class],
            'integer 1'            => [1, \ValueError::class],
        ];
    }

    public function booleanFeatureSettings()
    {
        return [
            'allow_analyses' => ['allow_analyses'],
            'allow_new_taken_tests_page' => ['allow_new_taken_tests_page'],
            'allow_new_co_learning' => ['allow_new_co_learning'],
            'allow_new_co_learning_teacher' => ['allow_new_co_learning_teacher'],
            'allow_creathlon' => ['allow_creathlon'],
            'allow_olympiade' => ['allow_olympiade'],
            'allow_olympiade_archive' => ['allow_olympiade_archive'],
        ];
    }

    /*
     * Utility methods
     */
    private function getSchoolLocationAndClearFeatureSetting()
    {
        $schoolLocation = SchoolLocation::first();
//        if ($schoolLocation->testPackage !== TestPackages::None) {
//            $schoolLocation->testPackage = false;
//        }
//        $schoolLocation->featureSettings()->delete();
        return $schoolLocation;
    }
}