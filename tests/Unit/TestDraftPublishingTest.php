<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Database\Seeders\CreathlonItemBankSeeder;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryTestTake;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Livewire\Teacher\PublishTestModal;
use tcCore\SchoolClass;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Test;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TestDraftPublishingTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

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
        $test = FactoryTest::create(
            ScenarioLoader::get('teacher1'),
            ['draft' => 1],
        )->addQuestions([
            FactoryQuestionOpenShort::create(),
            FactoryQuestionOpenShort::create(),
        ])
        ->getTestModel();

        $this->assertTrue($test->isDraft());

        foreach($test->testQuestions()->get()->map->question as $question) {
            $this->assertTrue($question->draft);
        }

        $test->publish()->save();

        $test->refresh();
        $this->assertTrue($test->isPublished());

        foreach($test->testQuestions()->get()->map->question as $question) {
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
        ])
        ->getTestModel();

        $this->assertTrue($test->isDraft());

        foreach($test->testQuestions()->get()->map->question as $question) {
            $this->assertTrue($question->draft);
        }
        $test->publish()->save();

        $test->refresh();
        $this->assertTrue($test->isPublished());

        foreach($test->testQuestions()->get()->map->question as $question) {
            $this->assertFalse($question->draft);
        }
    }
}
