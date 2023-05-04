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

class SmallsourceFibHelperTest extends TestCase
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
    public function sample_one_should_have_the_text_and_image_included()
    {
        $zipDir = '';
        $basePath = '';

        $question = simplexml_load_file(__DIR__.'/../../_fixtures_quayn_qti/smallsourceFibSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question, $this->test, $zipDir, $basePath);

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
                'note_type'              => 'NONE',
                'is_open_source_content' => '',
                'test_id'                => $this->test->id,
            ]
        );

        Request::filter($questionAttributes);

        $testQuestion = TestQuestion::store(
            $questionAttributes
        );

        // test question_large_sourcetext is appended to the question body
        $this->assertStringContainsString(
            'De begrenzing van het hoofdhaar bij het voorhoofd kan in een rechte',
            $testQuestion->question->getQuestionInstance()->getAttributes()['question']
        );

        $this->assertStringContainsString(
            '<img src="sources/Bvj_3gt_Th3_A_05.jpg" alt="Bvj_3gt_Th3_A_05.jpg">',
            $testQuestion->question->getQuestionInstance()->getAttributes()['question']
        );

        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('completion', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(2, $answers);
        $this->assertEquals('50', trim($answers[0]['answer']));
        $this->assertEquals('vijftig', trim($answers[1]['answer']));
    }
}
