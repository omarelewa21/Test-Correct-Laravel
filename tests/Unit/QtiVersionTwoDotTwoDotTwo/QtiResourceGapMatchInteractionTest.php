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
class QtiResourceGapMatchInteractionTest extends TestCase
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
            'ITM-330194',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330194.xml'),
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
            'title' => 'Klankschaal',
            'identifier' => 'ITM-330194',
            'label' => '32k6ce',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }

    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            ['correct_answer' => 'C G1', 'score_when_correct' => '1'],
            $this->instance->responseProcessing
        );
    }

    /** @test */
    public function it_should_select_the_correct_type_and_subtype_from_the_qti_factory()
    {
        $this->assertEquals(
            'MatchingQuestion',
            $this->instance->qtiQuestionTypeToTestCorrectQuestionType('type')
        );

        $this->assertEquals(
            'Matching',
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
                'baseType' => 'directedPair',
            ],
            'correct_response_attributes' => [
                'interpretation' => 'C G1&A G2&B G3',
            ],
            'values' => [
                 'C G1',
                 'A G2',
                 'B G3',
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
                    'href' => '../css/cito_generated_330194.css',
                    'type' => 'text/css',
                ],
            ],
            $this->instance->stylesheets
        );
    }

    /** @test */
    public function selectable_answers()
    {
        $this->assertEquals(3, $this->instance->getSelectableAnswers());
    }

    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<gapMatchInteraction id="gapMatchScoring" responseIdentifier="RESPONSE" shuffle="false">
  <gapText identifier="A" matchMax="1">
    <span>gehoor</span>
  </gapText>
  <gapText identifier="B" matchMax="1">
    <span>lucht</span>
  </gapText>
  <gapText identifier="C" matchMax="1">
    <span>klankschaal</span>
  </gapText>
  <table class="cito_genclass_330194_1">
    <colgroup>
      <col/>
      <col/>
    </colgroup>
    <tbody>
      <tr>
        <td class="cito_genclass_330194_2 cito_genclass_330194_3">
          <p>
            <strong>geluidsbron</strong>
          </p>
        </td>
        <td class="cito_genclass_330194_2 cito_genclass_330194_4">
          <p class="cito_genclass_330194_5"><span><gap identifier="G1" required="true"/></span>&#xA0;</p>
        </td>
      </tr>
      <tr>
        <td class="cito_genclass_330194_6 cito_genclass_330194_7">
          <p>
            <strong>geluidsontvanger</strong>
          </p>
        </td>
        <td class="cito_genclass_330194_6 cito_genclass_330194_8">
          <p class="cito_genclass_330194_9"><span><gap identifier="G2" required="true"/></span>&#xA0;</p>
        </td>
      </tr>
      <tr>
        <td class="cito_genclass_330194_10 cito_genclass_330194_11">
          <p>
            <strong>tussenstof</strong>
          </p>
        </td>
        <td class="cito_genclass_330194_10 cito_genclass_330194_12">
          <p class="cito_genclass_330194_13"><span><gap identifier="G3" required="true"/></span>&#xA0;</p>
        </td>
      </tr>
    </tbody>
  </table>
  <p>&#xA0;</p>
</gapMatchInteraction>
', $this->instance->interaction
);
    }




}
