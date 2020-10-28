<?php

namespace Tests\Unit\QtiV2dot2dot2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot0\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToSingleChoiceVersion250163Test extends TestCase
{
    use DatabaseTransactions;

    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::where('username', 'd1@test-correct.nl')->first());

        $resource = new Resource(
            'ITM-250163',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/250163.xml'),
            '1',
            'd472b88b-344d-4f18-b892-125597969d5d'
        );
        $this->instance = (new QtiResource($resource))->handle();
    }

    /** @test */
    public function it_can_read_load_xml_using_a_resource_250163()
    {
        $this->assertInstanceOf(\SimpleXMLElement::class, $this->instance->getXML());
    }


    /** @test */
    public function it_can_handle_the_item_body_250163()
    {
        $this->assertStringContainsString(
            'bonte vliegenvanger is een trekvogel',
            $this->instance->question_xml);
    }

    /** @test */
    public function it_can_find_correct_info_250163()
    {
        logger($this->instance->question_xml);
        $this->assertStringContainsString(
            'De bonte vliegenvangers die voorheen zuidelijker broedden, broeden nu in Nederland.',
            $this->instance->question_xml);
    }
}
