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

class QtiResourceToSimpleChoice3Test extends TestCase
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
            storage_path('../tests/_fixtures_woots_qti/QUE_2812152_1.xml'),
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
  <prompt>&lt;div&gt;Tot welke schakel in de voedselketen behoort de sprinkhaan op basis van de bovenstaande
                informatie?&lt;/div&gt;
            </prompt>
  <simpleChoice identifier="choice1">&lt;div&gt;tot de consumenten 1e orde&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice2">&lt;div&gt;tot de consumenten 2e orde&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice3">&lt;div&gt;tot de consumenten 3e orde&lt;/div&gt;</simpleChoice>
</choiceInteraction>',
            $this->instance->interaction);
    }

    /** @test */
    public function the_question_should_contain_all_document_fragments()
    {
        $this->assertEquals(
            '<div class="redactor-content custom-qti-style" data-js-katex="" data-js-redactor-content=""><div>De parasitaire
            paardenhaarworm (<em>Paragordius varius</em>) plant zich voort in ondiep water. Een vrouwtje
            legt zo&rsquo;n 6 miljoen eitjes waaruit na een week of drie de larven komen. De larven zwemmen vrij door het
            water tot ze samen met zo&ouml;plankton en watervlooien worden opgeslokt door een muggenlarve. In de muggenlarve
            boren de paardenhaarwormlarven zich door de wand van het maagdarmkanaal. <br></div><div class="question_prompt"><div>Tot welke schakel in de voedselketen behoort de sprinkhaan op basis van de bovenstaande
                informatie?</div>
            </div></div>
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
        $this->assertCount(3, $answerLinks);

        $this->assertEquals(
            $answerLinks->map(function ($link) {
                return Str::of($link->multipleChoiceQuestionAnswer->answer)->trim()->__toString();
            })->toArray(), [
                '<div>tot de consumenten 1e orde</div>',
                '<div>tot de consumenten 2e orde</div>',
                '<div>tot de consumenten 3e orde</div>',
            ]
        );
    }
}
