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

class QtiResourceToInlineChoiceTest extends TestCase
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
            'ITM-330011',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330011.xml'),
            '1',
            '42bf8f33-c198-4c76-befd-c4ac27153211'
        );
        $this->instance = (new QtiResource($resource))->handle();
    }

    /** @test */
    public function it_can_read_load_xml_using_a_resource()
    {
        $this->assertInstanceOf(\SimpleXMLElement::class, $this->instance->getXML());
    }

    /** @test */
    public function it_can_handle_item_attributes()
    {
        $this->assertEquals([
            'title' => 'Verwarmen',
            'identifier' => 'ITM-330011',
            'label' => '32k6cf',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }

    /** @test */
    public function the_question_xml_property_should_be_correct()
    {
        $this->assertStringContainsString(
            '[gasvormig|vast|?vloeibaar].',
            $this->instance->question_xml
        );

        // let op de punt van het einde van de zin moet achter de optie staan;
        $this->assertStringContainsString(
            '[?kookpunt|smeltpunt].',
            $this->instance->question_xml
        );

        $this->assertStringContainsString(
            'Je ziet een diagram van de temperatuur tegen de tijd.</p>',
            $this->instance->question_xml
        );


    }

    /** @test */
    public function it_should_select_the_correct_type_and_subtype_from_the_qti_factory()
    {
        $this->assertEquals(
            'CompletionQuestion',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('type')
        );

        $this->assertEquals(
            'multi',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('subtype')
        );

    }

    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => ['C', 'A'], 'score_when_correct' => '1'],
            $this->instance->responseProcessing
        );
    }

    /** @test */
    public function it_can_handle_correct_response()
    {
        $this->assertEquals([
            'attributes' => [
                'identifier' => 'RESPONSE',
                'cardinality' => 'single',
                'baseType' => 'identifier',
            ],
            'correct_response_attributes' => [
                'interpretation' => 'C',
            ],
            'values' => [
                'C',
            ],
            'outcome_declaration' => [
                'attributes' => [
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'baseType' => 'integer',
                ],
                'default_value' => '0',
            ],
        ], $this->instance->responseDeclaration['RESPONSE']
        );


    }

    /** @test */
    public function it_can_handle_stylesheets()
    {
        $this->assertEquals(
            [
                [
                    'href' => '../css/cito_itemstyle.css',
                    'type' => 'text/css',
                ],
                [
                    'href' => '../css/cito_userstyle.css',
                    'type' => 'text/css',
                ],

            ],
            $this->instance->stylesheets
        );
    }

    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<inlineChoiceInteraction id="I15b205e2-f35b-4c20-ab33-11564b1094eb" required="true" responseIdentifier="RESPONSE" shuffle="false">
  <inlineChoice identifier="A">
    <span>gasvormig</span>
  </inlineChoice>
  <inlineChoice identifier="B">
    <span>vast</span>
  </inlineChoice>
  <inlineChoice identifier="C">
    <span>vloeibaar</span>
  </inlineChoice>
</inlineChoiceInteraction>',
            $this->instance->interaction);
    }


}
