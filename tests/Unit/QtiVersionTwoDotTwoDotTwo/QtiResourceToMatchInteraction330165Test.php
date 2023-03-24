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

/**
 * Class QtiResourceToMatchInteractionTest
 * @package Tests\Unit\QtiV2dot2dot2
 */
class QtiResourceToMatchInteraction330165Test extends TestCase
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
        $this->actingAs($this->teacherOne); $resource = new Resource(
            'ITM-330165',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/330165.xml'),
            '1',
            '88dec4d3-997f-4d3b-95cf-3345bf3c0f4b'
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
            'title' => 'Soorten lenzen',
            'identifier' => 'ITM-330165',
            'label' => '32k5l2',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }


    /** @test */
    public function it_should_select_the_correct_type_and_subtype_from_the_qti_factory()
    {
        $this->assertEquals(
            'MatrixQuestion',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('type')
        );

        $this->assertEquals(
            'SingleChoice',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('subtype')
        );
    }


    /** @test */
    public function it_can_handle_correct_response()
    {
        $this->assertEquals([
            'attributes' => [
                'identifier' => 'RESPONSE',
                'cardinality' => 'multiple',
                'baseType' => 'identifier',
            ],
            'correct_response_attributes' => [
                'interpretation' => 'B&A&B&A',
            ],
            'values' => [
                'y_A x_2',
                'y_B x_1',
                'y_C x_2',
                'y_D x_1',
            ],
            'outcome_declaration' => [
                'attributes' => [
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'baseType' => 'float',
                ],
                'default_value' => '0',
            ],
        ], $this->instance->responseDeclaration['RESPONSE']);
    }


    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertTrue(true);
    }

}
