<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussing;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithTwoQuestions;
use tcCore\Http\Livewire\Teacher\CoLearning;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\TestCase;

class CoLearningTeacherTest extends TestCase
{
    use DatabaseTransactions;

    public $user;

    private $assertSeeStartScreenHtml = 'co-learning-panel';
    private $assertDontSeeStartScreenHtml = 'selid="co-learning-teacher-drawer"';

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->user = User::find(1486);
    }

    private function createTakenTestTake()
    {
        $testTake = FactoryScenarioTestTakeTaken::createTestTake(
            user: $this->user,
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $this->user)
        );

        return $testTake;
    }
    private function createDiscussingTestTake()
    {
        $testTake = FactoryScenarioTestTakeDiscussing::createTestTake(
            user: $this->user,
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $this->user)
        );
        $testTake->update([
            'test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING,
        ]);

        return $testTake;
    }

    /** @test */
    public function can_open_co_learning_and_see_startscreen()
    {
        $testTake = $this->createTakenTestTake();

        Livewire::withQueryParams(['started' => false])
            ->test(CoLearning::class, ['test_take' => $testTake->uuid])
            ->assertSeeHtml('co-learning-panel')
        ;
    }

    /** @test */
    public function co_learning_shows_start_screen_when_testTake_is_still_in_taken_status()
    {
        $testTake = $this->createTakenTestTake();

        $this->assertEquals(TestTakeStatus::STATUS_TAKEN, $testTake->test_take_status_id);

        //assert an 'TAKEN' testTake gets redirected to the start screen when trying to start colearning.
        Livewire::withQueryParams(['started' => true])
            ->test(CoLearning::class, ['test_take' => $testTake->uuid])
            ->assertSeeHtml($this->assertSeeStartScreenHtml)
            ->assertDontSeeHtml($this->assertDontSeeStartScreenHtml);
    }

    /** @test */
    public function an_discussing_test_take_can_reload_co_learning_without_going_to_the_start_screen()
    {
        $testTake = $this->createDiscussingTestTake();

        //required properties to qualify as a testtake that has started colearning
        $this->assertNotNull($testTake->discussing_question_id);
        $this->assertNotNull($testTake->discussion_type);
        $this->assertEquals(TestTakeStatus::STATUS_DISCUSSING, $testTake->test_take_status_id);

        //assert an discussing testTake can reload CoLearning without going to the start screen.
        Livewire::withQueryParams(['started' => true])
            ->test(CoLearning::class, ['test_take' => $testTake->uuid])
            ->assertDontSeeHtml('co-learning-panel')
            ->assertSeeHtml('selid="co-learning-teacher-drawer"');
    }
    /** @test */
    public function an_discussing_test_take_can_return_to_the_start_screen_with_querystring_started_as_false()
    {
        $testTake = $this->createDiscussingTestTake();

        //required properties to qualify as a testtake that has started colearning
        $this->assertNotNull($testTake->discussing_question_id);
        $this->assertNotNull($testTake->discussion_type);
        $this->assertEquals(TestTakeStatus::STATUS_DISCUSSING, $testTake->test_take_status_id);

        Livewire::withQueryParams(['started' => false])
            ->test(CoLearning::class, ['test_take' => $testTake->uuid])
            ->assertSeeHtml('co-learning-panel')
            ->assertDontSeeHtml('selid="co-learning-teacher-drawer"');
    }

    /** @test */
    public function can_open_co_learning_and_dont_see_startscreen()
    {
        $testTake = $this->createDiscussingTestTake();


        Livewire::withQueryParams(['started' => false])
            ->test(CoLearning::class, ['test_take' => $testTake->uuid])
            ->assertSee('CO-Learning');
    }
}