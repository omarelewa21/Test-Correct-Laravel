<?php

namespace Tests\Unit\QtiV2dot2dot2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot0\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceTypeGuesserTest extends TestCase
{
    /**
     * @test
     * @dataProvider resourceProvider
     */
    public function it_can_guess_the_item_type($identifier, $type, $href, $version, $guid, $type_to_guess)
    {
        $resource = new Resource($identifier, $type, storage_path($href), $version, $guid);
        $this->instance = (new QtiResource($resource))->handle();

        $this->assertEquals(
            $type_to_guess,
            $this->instance->itemType
        );
    }

    public function resourceProvider()
    {
        return [
            [
                'ITM-330001',
                'imsqti_item_xmlv2p2',
                '../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330001.xml',
                '1',
                '88dec4d3-997f-4d3b-95cf-3345bf3c0f4b',
                'matchInteraction',
            ], [
                'ITM-330008',
                'imsqti_item_xmlv2p2',
                '../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330008.xml',
                '1',
                'dd36d7c3-7562-4446-9874-4cc1cdd0dc38',
                'choiceInteraction',
            ], [
                'ITM-330011',
                'imsqti_item_xmlv2p2',
                '../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330011.xml',
                '1',
                '42bf8f33-c198-4c76-befd-c4ac27153211',
                'inlineChoiceInteraction',
            ],
        ];
    }

}
