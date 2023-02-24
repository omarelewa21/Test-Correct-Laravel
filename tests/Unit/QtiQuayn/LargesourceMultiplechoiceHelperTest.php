<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\QtiQuayn;


use Illuminate\Support\Str;
use tcCore\CompletionQuestion;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Controllers\QtiImportController;
use tcCore\Http\Requests\Request;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class LargesourceMultiplechoiceHelperTest extends TestCase
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

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceMultiplechoiceSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question, $this->test, $zipDir, $basePath);


        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('multi', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(4, $answers);
        $this->assertEquals('trefwoordenregister.', trim($answers[0]['answer']));
        $this->assertEquals('inhoudsopgave.', trim($answers[1]['answer']));
        $this->assertEquals('voorkant van een boek.', trim($answers[2]['answer']));
        $this->assertEquals('achterkant van een boek.', trim($answers[3]['answer']));
    }

    /** @test */
    public function sample_two_of_multiplechoice_question_has_answers()
    {
        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceMultiplechoiceSample2.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question, $this->test, $zipDir, $basePath);

        $questionAttributes = array_merge(
            $result->helper->getConvertedAr(),
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 9,
//                'subtype'                => 'multi',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
                'note_type'              => 'NONE',
//                'is_open_source_content' => '',
                'test_id'                => $this->test->id,
            ]
        );
        Request::filter($questionAttributes);

        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('multi', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(2, $answers);
        $this->assertEquals('Juist.', trim($answers[0]['answer']));
        $this->assertEquals('Onjuist.', trim($answers[1]['answer']));

        $this->assertInstanceOf(CompletionQuestion::class, $testQuestion->question);
    }
}
