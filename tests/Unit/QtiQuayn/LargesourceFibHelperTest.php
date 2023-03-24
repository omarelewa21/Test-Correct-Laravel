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
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Controllers\QtiImportController;
use tcCore\Http\Requests\Request;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class LargesourceFibHelperTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private User $teacherOne;
    private $stubTest;


    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('user');
    }


    /** @test */
    public function sample_one_should_have_the_text_included()
    {
        global $counter;
        $counter++;
        $this->actingAs($this->teacherOne);

        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceFibSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $stubTest = $this->getStubTest();

        $result = QtiImportController::parseQuestion($question, $stubTest, $zipDir, $basePath);

        $questionAttributes = array_merge(
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 9,
//                'subtype'                => 'completion',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
//                'note_type'              => 'NONE',
//                'is_open_source_content' => '',
                'test_id' => $stubTest->id,
            ],
            $result->helper->getConvertedAr(),
        );

        Request::filter($questionAttributes);

        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        // test question_large_sourcetext is appended to the question body
        $this->assertStringContainsString(
            '<p style="font-size:10px;">',
            $testQuestion->question->getQuestionInstance()->getAttributes()['question']
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(3, $answers);
        $this->assertEquals('Y', trim($answers[0]['answer']));
        $this->assertEquals('X', trim($answers[1]['answer']));
        $this->assertEquals('man', trim($answers[2]['answer']));
    }

    /** @test */
    public function sample_two_of_multiplechoice_question_has_answers()
    {
        global $counter;
        $counter++;
        $this->actingAs($this->teacherOne);

        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceFibSample2.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);
        $testStub = $this->getStubTest();
        $result = QtiImportController::parseQuestion($question, $testStub, $zipDir, $basePath);

        $questionAttributes = array_merge(
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 10,
//                'subtype'                => 'completion',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
//                'note_type'              => '',
//                'is_open_source_content' => '',
                'test_id' => $testStub->id,
            ],
            $result->helper->getConvertedAr(),
        );

        Request::filter($questionAttributes);
        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());

        $answers = $result->helper->getConvertedAr('answer');

        $this->assertCount(6, $answers);

        $this->assertInstanceOf(CompletionQuestion::class, $testQuestion->question);
    }

    /** @test */
    public function sample_three_of_multiplechoice_question_has_answers()
    {
        $this->actingAs($this->teacherOne);

        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceFibSample3.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $stubTest = $this->getStubTest();
        $result = QtiImportController::parseQuestion($question, $stubTest, $zipDir, $basePath);

        $questionAttributes = array_merge(
            $result->helper->getConvertedAr(),
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 9,
//                'subtype'                => 'completion',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
//                'note_type'              => '',
//                'is_open_source_content' => '',
                'test_id' => $stubTest->id,
            ]
        );
        Request::filter($questionAttributes);

        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(2, $answers);

        $this->assertInstanceOf(CompletionQuestion::class, $testQuestion->question);
    }

    /** @test */
    public function sample_four_of_multiplechoice_question_has_answers()
    {
        $this->actingAs($this->teacherOne);

        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceFibSample4.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $stubTest = $this->getStubTest();
        $result = QtiImportController::parseQuestion($question, $stubTest, $zipDir, $basePath);

        $questionAttributes = array_merge(
            $result->helper->getConvertedAr(),
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 9,
//                'subtype'                => 'completion',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
//                'note_type'              => '',
//                'is_open_source_content' => '',
                'test_id' => $stubTest->id,
            ]
        );
        Request::filter($questionAttributes);

        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(6, $answers);

        $this->assertInstanceOf(CompletionQuestion::class, $testQuestion->question);
    }

    /** @test */
    public function sample_five_of_multiplechoice_question_has_answers()
    {
        $this->actingAs($this->teacherOne);

        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceFibSample5.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $stubTest = $this->getStubTest();
        $result = QtiImportController::parseQuestion($question, $stubTest, $zipDir, $basePath);

        $questionAttributes = array_merge(
            $result->helper->getConvertedAr(),
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 9,
//                'subtype'                => 'completion',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
//                'note_type'              => '',
//                'is_open_source_content' => '',
                'test_id'                => $stubTest->id,
            ]
        );
        Request::filter($questionAttributes);

        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(8, $answers);

        $this->assertInstanceOf(CompletionQuestion::class, $testQuestion->question);
    }

    /** @test */
    public function sample_six_should_have_question_large_sourcetext_tag_merged_with_question_body_answers()
    {
        $this->actingAs($this->teacherOne);

        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__ . '/../../_fixtures_quayn_qti/largesourceFibSample6.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $stubTest = $this->getStubTest();
        $result = QtiImportController::parseQuestion($question, $stubTest, $zipDir, $basePath);

        $questionAttributes = array_merge(
            $result->helper->getConvertedAr(),
            [
//                'type'                   => 'CompletionQuestion',
//                'score'                  => 3,
//                'order'                  => 9,
//                'subtype'                => 'completion',
//                'maintain_position'      => '',
//                'discuss'                => '',
//                'decimal_score'          => '',
//                'add_to_database'        => '',
//                'attainments'            => '',
//                'note_type'              => '',
//                'is_open_source_content' => '',
                'test_id'                => $stubTest->id,
            ]
        );
        Request::filter($questionAttributes);


        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        $this->assertStringContainsString(
            'nschappers hebben in Xuchang',
            $testQuestion->question->getQuestionInstance()->getAttribute('question')
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(2, $answers);

        $this->assertInstanceOf(CompletionQuestion::class, $testQuestion->question);
    }


    private function getStubTest()
    {
        return FactoryTest::create($this->teacherOne)->getTestModel();

        $test = new Test();
        $test->subject_id = 1;
        $test->eduction_level_id = 1;
        $test->education_level_year = 1;
        return $test;
    }
}
