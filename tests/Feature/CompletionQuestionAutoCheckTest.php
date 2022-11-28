<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Answer;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswer;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\TestTake;
use Tests\TestCase;

class CompletionQuestionAutoCheckTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_should_correctly_transform_nbsp_to_space_in_the_base_helper()
    {
        $testTake = FactoryScenarioTestTakeTaken::createTestTake();
        $completionQuestion = $this->getCompletionQuestionWithAutoCheckEnabled($testTake);

        $cqaId = $completionQuestion->completionQuestionAnswers->first()->getKey();
        CompletionQuestionAnswer::whereId($cqaId)->update(['answer' =>'put them away']);

        $cqa = CompletionQuestionAnswer::whereId($cqaId)->first();

        $this->assertNotEquals($cqa->answer, 'put them away');
        $this->assertEquals(BaseHelper::transformHtmlCharsReverse($cqa->answer), 'put them away');
    }

    /** @test */
    public function it_should_correctly_check_answer_with_nbsp_value_compared_to_space_value()
    {
        $testTake = FactoryScenarioTestTakeTaken::createTestTake();
        $completionQuestion = $this->getCompletionQuestionWithAutoCheckEnabled($testTake);
        $totalScore = $completionQuestion->getQuestionInstance()->score;
        $cqaId = $completionQuestion->completionQuestionAnswers->first()->getKey();

        CompletionQuestionAnswer::whereId($cqaId)->update(['answer' =>'put them away']);
        Answer::whereQuestionId($completionQuestion->getKey())->update(['json' =>'{"0":"put them away","1":"blue"}']);
        $studentAnswer = Answer::whereQuestionId($completionQuestion->getKey())->first();
        $result = $completionQuestion->refresh()->checkAnswerCompletion($studentAnswer);

        $this->assertEquals($totalScore, $result);
    }

    private function getCompletionQuestionWithAutoCheckEnabled(TestTake $testTake)
    {
        $completionQuestion = CompletionQuestion::select('completion_questions.*')
            ->join('test_questions', 'test_questions.question_id', '=', 'completion_questions.id')
            ->join('tests', 'tests.id', '=', 'test_questions.test_id')
            ->where('tests.id', $testTake->test_id)
            ->where('subtype', 'completion')
            ->first();

        CompletionQuestion::where('id', $completionQuestion->getKey())->update(['auto_check_answer' => 1, 'auto_check_answer_case_sensitive' => 1]);
        return $completionQuestion->refresh();
    }
}
