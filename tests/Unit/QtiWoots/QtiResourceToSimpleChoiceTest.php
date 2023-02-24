<?php

namespace Tests\Unit\QtiWoots;

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

class QtiResourceToSimpleChoiceTest extends TestCase
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
            storage_path('../tests/_fixtures_woots_qti/QUE_2812145_1.xml'),
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
            ['correct_answer' => '', 'score_when_correct' => '1'],
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
                'basetype' => 'identifier',
            ],
            'correct_response_attributes' => [ ],
            'values' => [],
            'outcome_declaration' => [
                'attributes' => [
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'basetype' => 'float',
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
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<choiceInteraction maxChoices="1" responseIdentifier="RESPONSE" shuffle="false">
  <prompt>&lt;div&gt;Door mee te doen met de Biologie Olympiade ga je er mee akkoord dat jouw docent jouw
                gegevens aan ons doorgeeft. Wil je de toets maken, maar wil je om privacyredenen niet dat jouw docent je
                gegevens aan ons doorgeeft? Geef dat dan hieronder aan. Je doet dan niet meer mee aan de wedstrijd.&lt;/div&gt;
            </prompt>
  <simpleChoice identifier="choice1">&lt;div&gt;Ik doe&#xA0;mee aan de wedstrijd.&lt;/div&gt;</simpleChoice>
  <simpleChoice identifier="choice2">&lt;div&gt;Stuur mijn gegevens&#xA0;niet op. Ik doe &lt;strong&gt;niet&lt;/strong&gt;
                mee aan de wedstrijd.&lt;/div&gt;
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
            '<div>Door mee te doen met de Biologie Olympiade ga je er mee akkoord dat jouw docent jouw',
            ($instance->question)
        );

        $answerLinks = MultipleChoiceQuestionAnswerLink::where('multiple_choice_question_id', $instance->id)->get();
        $this->assertCount(2, $answerLinks);

        $this->assertEquals(
            $answerLinks->map(function ($link) {
                return $link->multipleChoiceQuestionAnswer->answer;
            })->toArray(),[
          '<div>Ik doe&nbsp;mee aan de wedstrijd.</div>
',
   '<div>Stuur mijn gegevens&nbsp;niet op. Ik doe <strong>niet</strong>
                mee aan de wedstrijd.</div>
']

        );
    }
}
