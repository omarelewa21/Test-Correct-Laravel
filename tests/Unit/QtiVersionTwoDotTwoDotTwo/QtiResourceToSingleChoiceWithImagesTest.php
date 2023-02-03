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

class QtiResourceToSingleChoiceWithImagesTest extends TestCase
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
            'ITM-330041',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/Test-maatwerktoetsen_v01/depitems/330041.xml'),
            '1',
            'e8f8b252-0433-425f-b79e-7805dd3ebdbf'
        );
        $this->instance = (new QtiResource($resource))->handle();
    }

    /** @test */
    public function it_can_read_load_xml_using_a_resource()
    {
        $this->assertInstanceOf(\SimpleXMLElement::class, $this->instance->getXML());
    }

    /** @test */
    public function it_can_upload_images_and_change_urls_in_answers()
    {
        collect($this->instance->answersWithImages)->each(function ($html) {
            $this->assertStringContainsString(
                '/questions/inlineimage/',
                $html
            );

            $dom = (new \DOMDocument());
            $dom->loadHTML($html);

            $arr = explode(
                '/',
                $dom->getElementsByTagname('img')->item(0)->getAttribute('src')
            );
            $this->assertFileExists(
                sprintf(
                    '%s/%s',
                    storage_path('inlineimages'),
                    end($arr)
                )
            );
        });
    }

    /** @test */
    public function it_can_handle_item_attributes()
    {
        $this->assertEquals([
            'title' => 'Practicum weerstand',
            'identifier' => 'ITM-330041',
            'label' => '32k6c2',
            'timeDependent' => 'false',
        ], $this->instance->attributes);

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
                    'href' => '../css/cito_generated_330041.css',
                    'type' => 'text/css',
                ],
            ],
            $this->instance->stylesheets
        );
    }

    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?>
<choiceInteraction class="four-columns " id="choiceInteraction1" maxChoices="1" responseIdentifier="RESPONSE" shuffle="false">
  <simpleChoice identifier="A">
    <p>
      <img alt="" height="115" id="Id-IMG_e64e45d0-e746-49af-9ffb-f9f1618dfb84" src="../img/mwt20nask1vmbo-330041-2.png" width="204"/>
    </p>
  </simpleChoice>
  <simpleChoice identifier="B">
    <p>
      <img alt="" height="134" id="Id-IMG_a8b74647-945b-4e42-845c-0e531d8c8c01" src="../img/mwt20nask1vmbo-330041-3.png" width="200"/>
    </p>
  </simpleChoice>
  <simpleChoice identifier="C">
    <p><img alt="" height="134" id="Id-IMG_8b3d85f3-84a3-4364-869f-3527334316c4" src="../img/mwt20nask1vmbo-330041-4.png" width="202"/>&#xA0;</p>
  </simpleChoice>
  <simpleChoice identifier="D">
    <p><img alt="" height="135" id="Id-IMG_cdc33c06-0511-48a8-9ad9-6ce75d2ca5a3" src="../img/mwt20nask1vmbo-330041-5.png" width="201"/>&#xA0;</p>
  </simpleChoice>
</choiceInteraction>',
            $this->instance->interaction);
    }
}
