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

class QtiResourceToSingleChoiceTest extends TestCase
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
            'ITM-330008',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330008.xml'),
            '1',
            'dd36d7c3-7562-4446-9874-4cc1cdd0dc38'
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
            'title' => 'Baksteentjes',
            'identifier' => 'ITM-330008',
            'label' => '32k6cb',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }

    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => 'C', 'score_when_correct' => '1'],
            $this->instance->responseProcessing
        );
    }

    /** @test */
    public function it_can_handle_inline_images()
    {
        collect($this->instance->images)->each(function($path){
            $pathWithoutQuestion = str_replace('/questions', '', $path);
            $pathWithImagesInsteadOfImage = str_replace('/inlineimage', '/inlineimages', $pathWithoutQuestion);
            $this->assertFileExists(storage_path($pathWithImagesInsteadOfImage));
        });
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
                    'href' => '../css/cito_generated_330008.css',
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
            'MultipleChoiceQuestion',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('type')
        );

        $this->assertEquals(
            'MultipleChoice',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('subtype')
        );

    }

    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<choiceInteraction id="choiceInteraction1" maxChoices="1" responseIdentifier="RESPONSE" shuffle="false">
  <simpleChoice identifier="A">
    <p>0,4 g</p>
  </simpleChoice>
  <simpleChoice identifier="B">
    <p>2,5 g</p>
  </simpleChoice>
  <simpleChoice identifier="C">
    <p>8,1 g</p>
  </simpleChoice>
</choiceInteraction>',
            $this->instance->interaction);
    }

    /** @test */
    public function it_can_add_the_question_to_the_database()
    {
        $instance = $this->instance->question->getQuestionInstance();

        $this->assertEquals('MultipleChoiceQuestion', $instance->type);
        $this->assertEquals(
            'MultipleChoice',
            $this->instance->question->subtype
        );

        $this->assertStringContainsString(
            'De steentjes zijn van baksteen. Het volume baksteen van elk steentje is 4,5 cm',
            ($instance->question)
        );

        $answerLinks = MultipleChoiceQuestionAnswerLink::where('multiple_choice_question_id', $instance->id)->get();
        $this->assertCount(3, $answerLinks);

        $this->assertEquals(
            $answerLinks->map(function ($link) {
                return $link->multipleChoiceQuestionAnswer->answer;
            })->toArray(), [
                '<p>0,4 g</p>
',
                '<p>2,5 g</p>
',
                '<p>8,1 g</p>
',
            ]
        );

        $correctLink = $answerLinks->first(function ($link) {
            return $link->multipleChoiceQuestionAnswer->score == 1;
        });


        $this->assertEquals(
            '<p>8,1 g</p>
',
            $correctLink->multipleChoiceQuestionAnswer->answer
        );
    }
}
