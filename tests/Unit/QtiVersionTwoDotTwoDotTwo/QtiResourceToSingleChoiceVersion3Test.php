<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiParser;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ScenarioLoader;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToSingleChoiceVersion3Test extends TestCase
{
  //  use DatabaseTransactions;

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
            'ITM-330065',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330065.xml'),
            '1',
            '391da546-d8d2-4e74-8123-330f4160004b'
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
            'title' => 'Raamalarm',
            'identifier' => 'ITM-330065',
            'label' => '32k6c7',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }

    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => 'A', 'score_when_correct' => '1'],
            $this->instance->responseProcessing
        );
    }

    /** @test */
    public function it_can_handle_inline_images()
    {
        collect($this->instance->images)->each(function ($path) {
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
                'interpretation' => 'A',
            ],
            'values' => [
                'A',
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
                    'href' => '../css/cito_generated.css',
                    'type' => 'text/css',
                ],
                [
                    'href' => '../css/cito_generated_330065.css',
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
    <p>alleen schakelschema I</p>
  </simpleChoice>
  <simpleChoice identifier="B">
    <p>alleen schakelschema II</p>
  </simpleChoice>
  <simpleChoice identifier="C">
    <p>zowel schakelschema I als II</p>
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
            'Welk schakelschema kan in het alarm zitten',
            ($instance->question)
        );

        $answerLinks = MultipleChoiceQuestionAnswerLink::where('multiple_choice_question_id', $instance->id)->get();
        $this->assertCount(3, $answerLinks);

        $correctLink = $answerLinks->first(function ($link) {
            return $link->multipleChoiceQuestionAnswer->score == 1;
        });

        $this->assertEquals(
            '<p>alleen schakelschema I</p>
',
            $correctLink->multipleChoiceQuestionAnswer->answer
        );
    }
}
