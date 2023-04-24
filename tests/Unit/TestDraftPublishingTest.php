<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Database\Seeders\CreathlonItemBankSeeder;
use Illuminate\Support\Facades\DB;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryTestTake;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioSchoolToetsenbakkerij;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Livewire\Teacher\PublishTestModal;
use tcCore\Question;
use tcCore\SchoolClass;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Test;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TestDraftPublishingTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolCreathlon::class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');
    }

    /** @test */
    public function can_publish_test()
    {
        $test = FactoryTest::create(
            ScenarioLoader::get('teacher1'),
            ['draft' => 1],
        )
            ->getTestModel();

        $this->assertTrue($test->fresh()->isDraft());
        $this->assertFalse($test->fresh()->isPublished());

        $test->publish()->save();

        $this->assertTrue($test->fresh()->isPublished());
    }

    /** @test */
    public function can_publish_test_questions()
    {
        auth()->login($this->user);

        $test = FactoryTest::create(
            ScenarioLoader::get('teacher1'),
            ['draft' => 1],
        )->addQuestions([
            FactoryQuestionOpenShort::create(),
            FactoryQuestionOpenShort::create(),
        ])->getTestModel();

        $this->assertTrue($test->isDraft());

        foreach ($test->testQuestions()->get()->map->question as $question) {
            $this->assertTrue($question->draft);
        }

        $test->publish()->save();

        $test->refresh();
        $this->assertTrue($test->isPublished());

        foreach ($test->testQuestions()->get()->map->question as $question) {
            $this->assertFalse($question->draft);
        }
    }

    /**
     * This asserts the two forms of publishing don't interfere.
     * @test
     */
    public function can_draft_publish_test_questions_as_content_source_author()
    {
        (new CreathlonItemBankSeeder)->run();

        $creathlonTeacher = User::whereUsername(config('custom.creathlon_school_author'))->first();
        auth()->login($creathlonTeacher);

        $test = FactoryTest::create(
            $creathlonTeacher,
            ['draft' => 1],
        )->addQuestions([
            FactoryQuestionOpenShort::create(),
            FactoryQuestionOpenShort::create(),
        ])->getTestModel();

        $this->assertTrue($test->isDraft());

        foreach ($test->testQuestions()->get()->map->question as $question) {
            $this->assertTrue($question->draft);
        }
        $test->publish()->save();

        $test->refresh();
        $this->assertTrue($test->isPublished());

        foreach ($test->testQuestions()->get()->map->question as $question) {
            $this->assertFalse($question->draft);
        }
    }

    /**
     * This asserts that a copy of a test that is both draft published (draft === 0) and contentSource published (scope = x_published),
     *  results in a copy that is not published in either way.
     * @test
     */
    public function duplicating_a_published_draft_and_content_source_test_results_in_a_not_published_copy()
    {
        (new CreathlonItemBankSeeder)->run();

        $creathlonTeacher = User::whereUsername(config('custom.creathlon_school_author'))->first();
        auth()->login($creathlonTeacher);

        $test = FactoryTest::create(
            $creathlonTeacher,
            ['draft' => 1],
        )->addQuestions([
            FactoryQuestionOpenShort::create(),
        ])
            ->getTestModel();

        $this->assertTrue($test->isDraft());

        foreach ($test->testQuestions()->get()->map->question as $question) {
            $this->assertTrue($question->draft);
        }
        $test->abbreviation = 'PUBLS';
        $test->publish();
        $test->save();

        $test->refresh();

        //original test and question is published
        $this->assertTrue($test->isPublished());
        $this->assertFalse($test->testQuestions()->first()->question->draft);

        //duplicating.
        $newTest = $test->userDuplicate([], auth()->id());

        //original test and question is still published
        $this->assertTrue($test->isPublished());
        $this->assertFalse($test->testQuestions()->first()->question->draft);

        //Duplicated test and question (if duplicated) are NOT published
        $this->assertTrue($newTest->isDraft());
        if ($test->testQuestions()->first()->question->id !== $newTest->testQuestions()->first()->question->id) {
            $this->assertTrue($newTest->testQuestions()->first()->question->draft);
        }

    }

    /**
     * This asserts that a copy of a test that is draft published (draft === 0) (but NOT contentSource published, scope = x_published),
     *  results in a copy that is not published in either way.
     * @test
     */
    public function duplicating_a_published_draft_test_results_in_a_not_published_copy()
    {
        (new CreathlonItemBankSeeder)->run();

        $creathlonTeacher = User::whereUsername(config('custom.creathlon_school_author'))->first();
        auth()->login($creathlonTeacher);

        $test = FactoryTest::create(
            $creathlonTeacher,
            ['draft' => 1],
        )->addQuestions([
            FactoryQuestionOpenShort::create(),
        ])
            ->getTestModel();

        $this->assertTrue($test->isDraft());

        foreach ($test->testQuestions()->get()->map->question as $question) {
            $this->assertTrue($question->draft);
        }
        $test->publish();
        $test->save();

        $test->refresh();

        //original test and question is published
        $this->assertTrue($test->isPublished());
        $this->assertFalse($test->testQuestions()->first()->question->draft);

        //duplicating.
        $newTest = $test->userDuplicate([], auth()->id());

        //original test and question is still published
        $this->assertTrue($test->isPublished());
        $this->assertFalse($test->testQuestions()->first()->question->draft);

        //Duplicated test and question (if duplicated) are NOT published
        $this->assertTrue($newTest->isDraft());
        if ($test->testQuestions()->first()->question->id !== $newTest->testQuestions()->first()->question->id) {
            $this->assertTrue($newTest->testQuestions()->first()->question->draft);
        }

    }
}
