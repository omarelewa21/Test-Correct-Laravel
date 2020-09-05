<?php

namespace Tests\Unit\QtiV2dot2dot2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\QtiModels\ExcelManifest;
use tcCore\QtiModels\QtiManifest;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QtiManifestTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_load_the_excel_file()
    {
        $file = storage_path('../tests/_fixtures_qti/assessments.xlsx');
        $resourceList = ((new ExcelManifest($file))->getTestWithResourceList());
        collect($resourceList)->each(function ($resource) {
            $this->assertCount(5, collect($resource['items']));
        });

        $this->assertCount(
            19,
            collect($resourceList)->filter(function ($resource) {
                return $resource['level'] === 'havo';
            })
        );

        $this->assertCount(
            29,
            collect($resourceList)->filter(function ($resource) {
                return $resource['level'] === 'vwo';
            })
        );
    }
}
