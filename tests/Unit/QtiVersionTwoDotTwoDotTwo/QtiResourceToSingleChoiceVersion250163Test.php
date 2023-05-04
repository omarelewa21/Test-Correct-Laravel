<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToSingleChoiceVersion250163Test extends TestCase
{

    private $instance;
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private User $teacherOne;
    private $test;
    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('user');
        $this->test = FactoryTest::create($this->teacherOne)->getTestModel();
        $this->actingAs($this->teacherOne);

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
