<?php

namespace Tests\Unit\QtiWoots;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
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

class QtiResourceToSimpleChoice2Test extends TestCase
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
            'QUE_2812145_1',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_woots_qti/QUE_2812148_1.xml'),
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
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => 'choice3', 'score_when_correct' => '1'],
            $this->instance->responseProcessing
        );
    }


    /** @test */
    public function it_can_handle_correct_response()
    {
        $this->assertEquals([
            'attributes'                  => [
                'identifier'  => 'RESPONSE',
                'cardinality' => 'single',
                'basetype'    => 'identifier',
            ],
            'correct_response_attributes' => [],
            'values'                      => ['choice3'],
            'outcome_declaration'         => [
                'attributes'    => [
                    'identifier'  => 'SCORE',
                    'cardinality' => 'single',
                    'basetype'    => 'float',
                ],
                'default_value' => '0',
            ],
        ], $this->instance->responseDeclaration['RESPONSE']);
    }

    /** @test */
    public function it_can_handle_stylesheets()
    {
        $this->assertEquals(
            [],
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
    public function it_can_extract_the_item_interaction()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<choiceInteraction maxChoices="1" responseIdentifier="RESPONSE" shuffle="false">
  <prompt>&lt;div&gt;Hoe groot is de kans dat de eerste kitten uit dit nestje een Bambino Sphinx is? &lt;/div&gt;</prompt>
  <simpleChoice identifier="choice1">&lt;div&gt;3/16&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice2">&lt;div&gt;1/8&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice3">&lt;div&gt;1/6&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice4">&lt;div&gt;2/3&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice5">&lt;div&gt;3/4&lt;/div&gt;</simpleChoice>
</choiceInteraction>',
            $this->instance->interaction);
    }

    /** @test */
    public function the_question_should_contain_all_document_fragments()
    {
        $this->assertEquals(
            '<div class="redactor-content custom-qti-style" data-js-katex="" data-js-redactor-content=""><div><p>Het
            fokken op extreme uiterlijke kenmerken van dieren, zoals bij de Bambino Sphinx-kat, kan leiden tot ernstige
            gezondheids- en welzijnsproblemen. Carola Schouten, minister van Landbouw, Natuur en Voedselkwaliteit, wil
            daarom met meer voorlichting voorkomen dat mensen zo&rsquo;n haarloze kat met te korte poten aanschaffen.</p></div><div class="question_prompt"><div>Hoe groot is de kans dat de eerste kitten uit dit nestje een Bambino Sphinx is? </div></div></div>
',
            $this->instance->question_xml
        );

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

        $answerLinks = MultipleChoiceQuestionAnswerLink::where('multiple_choice_question_id', $instance->id)->get();
        $this->assertCount(5, $answerLinks);

        $this->assertEquals(
            $answerLinks->map(function ($link) {
                return Str::of($link->multipleChoiceQuestionAnswer->answer)->trim()->__toString();
            })->toArray(), [
                '<div>3/16</div>',
                '<div>1/8</div>',
                '<div>1/6</div>',
                '<div>2/3</div>',
                '<div>3/4</div>',
          ]
        );
    }
}
