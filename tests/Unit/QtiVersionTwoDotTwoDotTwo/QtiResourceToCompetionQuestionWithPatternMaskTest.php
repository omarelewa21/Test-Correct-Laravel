<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ScenarioLoader;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;

use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToCompetionQuestionWithPatternMaskTest extends TestCase
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
            'ITM-testitem_simpele_formule_editor_voor_invoer',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/130008.xml'),
            '1',
            '3ef7b0ad-6417-433f-8012-efd1f544dfc6'
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
            'title' => '130008 geldschepping chartaal en giraal geld',
            'identifier' => 'ITM-130008',
            'label' => '32k6yu',
            'timeDependent' => 'false',
        ], $this->instance->attributes);
    }

    /** @test */
    public function it_can_guess_item_type()
    {
        $this->assertEquals(
            'textEntryInteraction',
            $this->instance->itemType
        );
    }


    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => '750', 'score_when_correct' => '1'],
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
                'baseType' => 'integer',
            ],
            'correct_response_attributes' => [
                'interpretation' => '750',
            ],
            'values' => [
                '750',
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
                [
                    'href' => '../css/cito_generated.css',
                    'type' => 'text/css',
                ],
            ],
            $this->instance->stylesheets
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
            'completion',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('subtype')
        );

    }

    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<textEntryInteraction expectedLength="5" patternMask="^-?([0-9]{1,4})?$" responseIdentifier="RESPONSE"/>
',
            $this->instance->interaction);
    }

    /** @test */
    public function it_can_add_the_question_to_the_database()
    {
        $instance = $this->instance->question->getQuestionInstance();

    $this->assertEquals('CompletionQuestion', $instance->type);
        $this->assertEquals(
            'completion',
            $this->instance->question->subtype
        );

        $this->assertStringContainsString(
            'De algemene bank ALB moet',
            ($instance->question)
        );

        $this->assertStringContainsString(
            '[1]',
            ($instance->question)
        );

    }


}
