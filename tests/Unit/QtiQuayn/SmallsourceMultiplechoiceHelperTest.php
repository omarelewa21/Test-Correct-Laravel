<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\QtiQuayn;


use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Controllers\QtiImportController;
use tcCore\Test;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class SmallsourceMultiplechoiceHelperTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private User $teacherOne;
    private $test;
    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('user');
        $this->test = FactoryTest::create($this->teacherOne)->getTestModel();
        $this->actingAs($this->teacherOne);
    }
    /** @test */
    public function a_multiplechoice_questions_has_answers()
    {
        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__.'/../../_fixtures_quayn_qti/smallsourceMultiplechoiceSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question,$this->test,$zipDir, $basePath);

        $helper = $result->helper;
        $this->assertEquals('MultipleChoiceQuestion', $helper->getType());
        $this->assertEquals('MultipleChoice', $helper->getSubType());
        $answers = $helper->getConvertedAr('answer');
        $this->assertCount(6, $answers);
        $this->assertEquals('door de afbeelding op de voorkant te bekijken.', trim($answers[0]['answer']));
        $this->assertEquals('door de titel te lezen en de naam van de schrijver.', trim($answers[1]['answer']));
        $this->assertEquals('door op de achterkant van het boek te kijken.', trim($answers[2]['answer']));
        $this->assertEquals('door de eerste bladzijde te lezen of een ander fragment.', trim($answers[3]['answer']));
    }

    /** @test */
    public function example_two_has_an_image_has_answers()
    {
        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__.'/../../_fixtures_quayn_qti/smallsourceMultiplechoiceSample2.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question,$this->test,$zipDir, $basePath);

        $helper = $result->helper;
        $this->assertEquals('CompletionQuestion', $helper->getType());
        $this->assertEquals('multi', $helper->getSubType());
        $answers = $helper->getConvertedAr('answer');
        $this->assertCount(3, $answers);
        $this->assertEquals('Alleen door een verschil in erfelijke eigenschappen.', trim($answers[0]['answer']));
        $this->assertEquals('Alleen door invloeden uit het milieu.', trim($answers[1]['answer']));
        $this->assertEquals('Zowel door een verschil in erfelijke eigenschappen als door invloeden uit het milieu.', trim($answers[2]['answer']));
        $this->assertStringContainsString(
            'sources/Bvj_3gt_Th3_A_01.jpg',
            $helper->getConvertedAr('question')
        );
    }
}
