<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use tcCore\QtiModels\ExcelManifest;
use Tests\TestCase;

class QtiExcelManifestTest extends TestCase
{
    /** @test */
    public function it_can_load_the_excel_file()
    {
        $file = storage_path('../tests/_fixtures_qti/assessments.xlsx');
        $resourceList = ((new ExcelManifest($file))->getTestListWithResources());
        collect($resourceList)->each(function ($resource) {
            $this->assertCount(5, collect($resource['items']));
        });

        $this->assertEquals(
          '1a - Procenten | Rekenen | vwo, havo',
            collect($resourceList)->first()['test']
        );

        $this->assertCount(
            19,
            collect($resourceList)->filter(function ($resource) {
                return collect($resource['levels'])->contains( 'havo');
            })
        );

        $this->assertCount(
            29,
            collect($resourceList)->filter(function ($resource) {
                return collect($resource['levels'])->contains('vwo');
            })
        );
    }

    /** @test */
    public function get_highest_level()
    {
        $this->assertEquals('vwo', ExcelManifest::getHighestEducationLevel(collect(['vwo', 'havo'])));
        $this->assertEquals('vwo', ExcelManifest::getHighestEducationLevel(collect(['havo', 'vwo'])));
        $this->assertEquals('gl/tl', ExcelManifest::getHighestEducationLevel(collect(['gl/tl', 'kb'])));
        $this->assertEquals('havo', ExcelManifest::getHighestEducationLevel(collect(['havo', 'gl/tl'])));
        $this->assertEquals('vwo', ExcelManifest::getHighestEducationLevel(collect(['gl/tl', 'havo', 'vwo'])));
    }
}
