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

class QtiResourceGapMatchInteraction2Test extends TestCase
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
            storage_path('../tests/_fixtures_qti/130125.xml'),
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
            'title' => '130125 structuurontwikkeling economie',
            'identifier' => 'ITM-130125',
            'label' => '32k9as',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

    }

    /** @test */
    public function it_can_handle_response_processing()
    {
        $this->assertEquals(
            [
                'correct_answer' => 'B G1',
                'score_when_correct' => '1',
            ],
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
        $this->assertEquals(['RESPONSE' => [
            'attributes' => [
                'identifier' => 'RESPONSE',
                'cardinality' => 'multiple',
                'baseType' => 'directedPair',
            ],
            'correct_response_attributes' => [
                'interpretation' => 'B G1&C G2&A G3',
            ],
            'values' => [
                'B G1',
                'C G2',
                'A G3',
            ],
            'outcome_declaration' => [
                'attributes' => [
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'baseType' => 'float',
                ],
                'default_value' => '0',
            ],
        ]
        ], $this->instance->responseDeclaration);
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
    <span>
      <span>
        <span>
          <span>
            <span>
              <span>De concurrentiepositie verbetert.</span>
            </span>
          </span>
        </span>
      </span>
    </span>
  </gapText>
  <gapText identifier="B" matchMax="1">
    <span>
      <span>
        <span>
          <span>De kosten per product dalen.</span>
        </span>
      </span>
    </span>
  </gapText>
  <gapText identifier="C" matchMax="1">
    <span>
      <span>
        <span>De verkoopprijzen worden verlaagd.</span>
      </span>
    </span>
  </gapText>
  <p>
    <span>1 De arbeidsproductiviteit stijgt.</span>
  </p>
  <p>
    <span>2
                                    <span><gap identifier="G1" required="true"/></span>
                                </span>
  </p>
  <p>
    <span>3
                                    <span><gap identifier="G2" required="true"/></span>
                                </span>
  </p>
  <p>
    <span>4
                                    <span><gap identifier="G3" required="true"/></span>
                                </span>
  </p>
  <p>
    <span>5 De omzet neemt toe.</span>
  </p>
</gapMatchInteraction>

', $this->instance->interaction
        );
    }


}
