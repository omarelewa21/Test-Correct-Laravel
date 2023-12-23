<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenOneQuestion;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Factories\Questions\FactoryQuestionMatchingMatching;
use tcCore\Http\Helpers\Normalize;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TestRatingZeroScoredTest extends TestCase
{
    use DatabaseTransactions;

    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;
    protected $teacher;
    protected $studentOne;
    protected $assessmentPage;
    protected $questions;
    protected $testTake;
    protected $test;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
        $this->actingAs($this->teacher);
        ActingAsHelper::getInstance()->setUser($this->teacher);

        // construct a test with one question of score 0
        $this->test = FactoryTest::create($this->teacher)->addQuestions([
            FactoryQuestionMatchingMatching::create()
                ->setProperties([
                    'question' => '<p>question</p>',
                    'note_type' => 'TEXT',
                ])
                ->setScore(0)
        ])->getTestModel();

        $this->testTake = FactoryScenarioTestTakeTakenOneQuestion::createTestTake($this->teacher, test: $this->test);
        $this->testTake->show_results = Carbon::now()->addDay();
        $this->testTake->save();


        $this->questions = $this->testTake->test->getFlatQuestionList();
    }

    /** @test */
    public function normalizing_test_with_good_per_point_of_zero_should_return_empty_results()
    {
        $this->assertEquals($this->testTake->test->getTotalScore(), 0.0);

        $request = collect([
            'ignore_questions' => [],
            'preview'          => true,
            'ppp'              => 0.0
        ]);
        $normalize = new Normalize($this->testTake, $request);
        $res = $normalize->normBasedOnGoodPerPoint();

        $this->assertEquals($res, collect([]));
    }


    /** @test */
    public function normalizing_errors_per_point_of_zero_should_return_empty_results()
    {
        $this->assertEquals($this->testTake->test->getTotalScore(), 0.0);

        $request = collect([
            'ignore_questions' => [],
            'preview'          => true,
            'epp'              => 0.0
        ]);
        $normalize = new Normalize($this->testTake, $request);
        $res = $normalize->normBasedOnErrorsPerPoint();

        $this->assertEquals($res, collect([]));
    }

    /** @test */
    public function normalizing_average_should_return_empty_results()
    {
        $this->assertEquals($this->testTake->test->getTotalScore(), 0.0);

        $request = collect([
            'ignore_questions' => [],
            'preview'          => true,
            'wanted_average'   => 1.0
        ]);
        $normalize = new Normalize($this->testTake, $request);
        $res = $normalize->normBasedOnAverageMark();

        $this->assertEquals($res, collect([]));
    }

    /** @test */
    public function normalizing_n_term_should_return_empty_results()
    {
        $this->assertEquals($this->testTake->test->getTotalScore(), 0.0);

        $request = collect([
            'ignore_questions' => [],
            'preview'          => true,
            'n_term'           => 1.0
        ]);
        $normalize = new Normalize($this->testTake, $request);
        $res = $normalize->normBasedOnNTerm();

        $this->assertEquals($res, collect([]));
    }

    /** @test */
    public function normalizing_cesuur_should_return_empty_results()
    {
        $this->assertEquals($this->testTake->test->getTotalScore(), 0.0);

        $request = collect([
            'ignore_questions' => [],
            'preview'          => true,
            'n_term'           => 1.0,
            'pass_mark'        => 1.0
        ]);
        $normalize = new Normalize($this->testTake, $request);
        $res = $normalize->normBasedOnNTermAndPassMark();

        $this->assertEquals($res, collect([]));
    }
}