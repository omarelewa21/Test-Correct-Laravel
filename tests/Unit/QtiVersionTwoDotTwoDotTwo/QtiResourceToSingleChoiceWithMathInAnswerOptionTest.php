<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToSingleChoiceWithMathInAnswerOptionTest extends TestCase
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
            'Test_item_370020b',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/Test item 370020b.xml'),
            '1',
            'a985ac61-5dde-439f-9406-97825ecab2d6'
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
            'title' => '2.8-05',
            'identifier' => 'ITM-Test_item_370020b',
            'label' => '32k6cx',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }

    /** @test */
    public function it_should_strip_the_m_name_space_from_the_xml()
    {
        $this->assertEquals(
            0,
            substr_count($this->instance->xml_string, '<m:')
        );

        $this->assertEquals(
            0,
            substr_count($this->instance->xml_string, '</m:')
        );
    }
//    /** @test */
//    public function it_should_add_the_xmlns_for_math_ml_to_the_body()
//    {
//        $this->assertEquals(
//            1,
//            substr_count($this->instance->xml_string, 'xmlns="http://www.w3.org/1998/Math/MathML"')
//        );
//
//    }

    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => 'A', 'score_when_correct' => '1'],
            $this->instance->responseProcessing
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
                'interpretation' => 'A&B&D',
            ],
            'values' => [
                'A',
                'B',
                'D',
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
                    'href' => '../css/cito_generated_Testitem370020b.css',
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
    public function it_should_return_five_selectable_answers()
    {
        $this->assertEquals(
            5,
            $this->instance->getSelectableAnswers()
        );

    }

    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<choiceInteraction id="choiceInteraction1" maxChoices="5" responseIdentifier="RESPONSE" shuffle="false">
  <simpleChoice identifier="A">
    <p>met 0,2 vermenigvuldigen</p>
  </simpleChoice>
  <simpleChoice identifier="B">
    <p>
      <span>met&#xA0;</span>
      <span><span><math><mfrac><mn mathsize="18px">1</mn><mn mathsize="18px">5</mn></mfrac></math>&#xA0;</span>vermenigvuldigen</span>
      <span/>
    </p>
  </simpleChoice>
  <simpleChoice identifier="C">
    <p>door 20 delen</p>
  </simpleChoice>
  <simpleChoice identifier="D">
    <p>door 5 delen</p>
  </simpleChoice>
  <simpleChoice identifier="E">
    <p>door&#xA0;<math><mfrac><mn mathsize="18px">1</mn><mn mathsize="18px">5</mn></mfrac></math>&#xA0;delen</p>
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
            'Geef alle goede mogelijkheden aan.',
            ($instance->question)
        );

        $answerLinks = MultipleChoiceQuestionAnswerLink::where('multiple_choice_question_id', $instance->id)->get();
        $this->assertCount(5, $answerLinks);

        $correctAnswers = $answerLinks->filter(function ($link) {
            return $link->multipleChoiceQuestionAnswer->score == 1;
        })->map(function ($link) {
            return $link->multipleChoiceQuestionAnswer->answer;
        });

        $this->assertCount(3, $correctAnswers);
        [
            '<p>met 0,2 vermenigvuldigen</p>\n',
            '<p><span>met&nbsp;</span><span><span><math><mfrac><mn mathsize="18px">1</mn><mn mathsize="18px">5</mn></mfrac></math>&nbsp;</span>vermenigvuldigen</span><span></span></p>\n',
            '<p>door 5 delen</p>\n',
        ];
    }
}
