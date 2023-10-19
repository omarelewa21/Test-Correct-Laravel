<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Test;
use tcCore\TestQuestion;

class PublishTestModal extends TCModalComponent
{
    public $testUuid;
    public $showInfo;
    public $knowledgebankUrl;
    public $testErrors = [];

    public function mount($testUuid)
    {
        $this->testUuid = $testUuid;
        $this->showInfo = !Auth::user()->has_published_test;
        $this->handleErrorsInTest();
        $this->knowledgebankUrl = sprintf('%s/publiceren', config('app.knowledge_bank_url'));
    }

    public function render()
    {
        return view('livewire.teacher.publish-test-modal');
    }

    public function handle()
    {
        Test::findByUuid($this->testUuid)->publish()->save();
        Auth::user()->has_published_test = true;

        $this->emit('test-updated');

        $this->closeModal();
    }

    private function handleErrorsInTest()
    {
        $test = Test::findByUuid($this->testUuid);
        if(!Gate::allows('canViewTestDetails',[$this->test])){
            $this->forceClose()->closeModal();
            return;
        }
        $duplicateIds = $test->getDuplicateQuestionIds();
        if (filled($duplicateIds)) {
            $this->handleDuplicateIdsErrors($test, $duplicateIds);
        }

        if ($test->hasTooFewQuestionsInCarousel()) {
            $this->handleTooFewQuestionsErrors($test);
        }

        if ($test->hasNotEqualScoresForSubQuestionsInCarousel()) {
            $this->handleNotEqualScoresErrors($test);
        }
    }

    private function getJoinedValuesAsString($collection): string
    {
        return collect($collection)->join(', ', ' ' . __('general.en') . ' ');
    }

    private function getGroupQuestionNamesWithInsufficientSubQuestions(Test $test)
    {
        return TestQuestion::select('gq.name')
            ->join('questions as q', 'test_questions.question_id', '=', 'q.id')
            ->join('group_questions as gq', 'gq.id', '=', 'q.id')
            ->where('test_questions.test_id', $test->getKey())
            ->where('q.type', 'GroupQuestion')
            ->whereNot(
                'gq.number_of_subquestions',
                function ($query) {
                    $query->selectRaw('count(id)')
                        ->from('group_question_questions')
                        ->whereRaw('group_question_id = gq.id');
                }
            )->pluck('name');
    }

    private function getGroupQuestionNamesWithUnequalSubQuestionScores(Test $test)
    {
        return DB::query()->selectRaw('scores.id, gq.name, count(scores.id) as count')
            ->fromSub(
                GroupQuestionQuestion::selectRaw('group_question.id, sub_question.score')
                    ->join('test_questions as tq', 'tq.question_id', '=', 'group_question_questions.group_question_id')
                    ->join('questions as group_question', 'group_question_questions.group_question_id', '=', 'group_question.id')
                    ->join('questions as sub_question', 'group_question_questions.question_id', '=', 'sub_question.id')
                    ->where('tq.test_id', $test->getKey())
                    ->where('group_question.type', 'GroupQuestion')
                    ->groupByRaw('group_question.id, sub_question.score')
                , 'scores'
            )
            ->join('group_questions as gq', 'gq.id', '=', 'scores.id')
            ->groupBy('id', 'name')
            ->get()
            ->map(fn($group) => $group->count > 1 ? "'$group->name'" : false)
            ->filter(fn($group) => $group !== false);
    }

    /**
     * @param Test $test
     * @param Collection $duplicateIds
     * @return void
     */
    private function handleDuplicateIdsErrors(Test $test, Collection $duplicateIds): void
    {
        $questionOrderInTest = $test->getQuestionOrderList();

        $order = $duplicateIds->map(function ($questionId) use ($questionOrderInTest) {
            return $questionOrderInTest[$questionId] ?? false;
        })->sort();

        $this->testErrors[__('test.duplicate_questions')] = trans_choice('test.duplicate_question_error_message', $order->toArray(), ['questions' => $this->getJoinedValuesAsString($order)]);
    }

    /**
     * @param Test $test
     * @return void
     */
    private function handleTooFewQuestionsErrors(Test $test): void
    {
        $carouselsWithTooFewQuestions = $this->getGroupQuestionNamesWithInsufficientSubQuestions($test)->map(fn($name) => "'$name'");

        $this->testErrors[__('test.carousel_too_few_questions')] = trans_choice('cms.carousel_not_enough_questions_with_names', $carouselsWithTooFewQuestions->toArray(), ['questions' => $this->getJoinedValuesAsString($carouselsWithTooFewQuestions)]);
    }

    /**
     * @param Test $test
     * @return void
     */
    private function handleNotEqualScoresErrors(Test $test): void
    {
        $carouselsWithUnequalScores = $this->getGroupQuestionNamesWithUnequalSubQuestionScores($test);
        $this->testErrors[__('test.carousel_unequal_scores')] = trans_choice('cms.carousel_subquestions_scores_differ_with_names', $carouselsWithUnequalScores->toArray(), ['questions' => $this->getJoinedValuesAsString($carouselsWithUnequalScores)]);
    }
}
