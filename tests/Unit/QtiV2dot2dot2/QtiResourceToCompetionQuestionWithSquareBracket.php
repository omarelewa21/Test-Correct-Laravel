<?php

namespace Tests\Unit\QtiV2dot2dot2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot0\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceToCompetionQuestionWithSquareBracketTest extends TestCase
{
    use DatabaseTransactions;

    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::where('username', 'd1@test-correct.nl')->first());

        $resource = new Resource(
            'ITM-210155',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/210155.xml'),
            '2',
            'f8f94557-148e-4b00-8b65-3cf96e5583c6'
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
            ['correct_answer' => ['183.2','184.4'], 'score_when_correct' => '1'],
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
                'baseType' => 'string',
            ],
            'correct_response_attributes' => [
                'interpretation' => '183.2',
            ],
            'values' => [
                '183.2',
            ],
            'outcome_declaration' => [
                'attributes' => [
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'baseType' => 'float',
                ],
                'default_value' => '0',
            ],
        ], $this->instance->responseDeclaration);
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
<textEntryInteraction expectedLength="9" patternMask="^-?([0-9]{1,5})?(([\,])([0-9]{0,3}))?$" responseIdentifier="RESPONSE"/>
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
            'Bereken het 95%-betrouwbaarheidsinterval voor',
            ($instance->question)
        );

        $this->assertStringContainsString(
            '[1]',
            ($instance->question)
        );
        $this->assertStringContainsString(
            '[2]',
            ($instance->question)
        );
    }

    /** @test */
    public function question_text_doesn_t_contain_square_brackets_other_then_placeholders_for_question()
    {
        $questionHtml = json_decode($this->instance->question)->question;
        $questionHtml = str_replace(['[1]', '[2]'], ['',''],$questionHtml);
//        $this->assertStringNotContainsString('[', $questionHtml);
        $this->assertStringContainsString('<span class="bracket-open">', $questionHtml);
//        $this->assertStringNotContainsString(']', $questionHtml);
        $this->assertStringNotContainsString('<span class="bracket-close">', $questionHtml);
    }

    /** @test */
    public function the_style_sheets_are_stripped_from_type_text_radio_checkbox()
    {
         $questionHtml = json_decode($this->instance->question)->question;
//        $this->assertStringContainsString('input="text"', $questionHtml);
        $this->assertStringContainsString('input="radio"', $questionHtml);

    }

    /** @test */
    public function question_xml_contains_stylesheet_data()
    {
        $this->assertStringContainsString(
            '<style>',
            $this->instance->question_xml
        );
    }


}
