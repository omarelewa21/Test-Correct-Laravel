<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot0\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToSingleChoiceWithMathTest extends TestCase
{
    use DatabaseTransactions;

    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::where('username', 'd1@test-correct.nl')->first());

        $resource = new Resource(
            'ITM-testitem_simpele_formule_editor_voor_invoer',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/testitem simpele formule editor voor invoer.xml'),
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
            'title' => 'testitem simpele formule editor voor invoer',
            'identifier' => 'ITM-testitem_simpele_formule_editor_voor_invoer',
            'label' => '32k6cd',
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
    /** @test */
    public function it_should_add_the_xmlns_for_math_ml_to_the_body()
    {
        $this->assertEquals(
            1,
            substr_count($this->instance->xml_string, 'xmlns="http://www.w3.org/1998/Math/MathML"')
        );

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
                    'href' => '../css/cito_generated_testitemsimpeleformuleeditorvoorinvoer.css',
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
    <p>alternatief A</p>
  </simpleChoice>
  <simpleChoice identifier="B">
    <p>alternatief B</p>
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
            'breuk grootte aangepast (groter):',
            ($instance->question)
        );

        $answerLinks = MultipleChoiceQuestionAnswerLink::where('multiple_choice_question_id', $instance->id)->get();
        $this->assertCount(2, $answerLinks);

        $correctLink = $answerLinks->first(function ($link) {
            return $link->multipleChoiceQuestionAnswer->score == 1;
        });

        $this->assertEquals(
            '<p>alternatief A</p>
',
            $correctLink->multipleChoiceQuestionAnswer->answer
        );
    }
}
