<?php


namespace tcCore\Http\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Livewire\Teacher\TestTakeOverview;
use tcCore\RelationQuestion;
use tcCore\TestTake;

trait WithTestTakeInteractions
{
    public function openTestTakeDetail($testTakeUuid, ?string $pageAction = null): null|RedirectResponse|Redirector
    {
        $testTake = TestTake::whereUuid($testTakeUuid)->with('test:id,uuid')->first();
        $pageNumber = session()->get(TestTakeOverview::PAGE_NUMBER_SESSION_KEY);
        
        if ($testTake->archived) {
            $this->dispatchBrowserEvent('notify', ['message' => __('test_take.unarchive_test_take_first'), 'type' => 'error']);
            return null;
        }

        if (!settings()->canUseRelationQuestion() && $testTake->test->containsSpecificQuestionTypes(RelationQuestion::class)) {
            $this->emit('openModal', 'teacher.test-take.relation-question-no-access-modal');
            return null;
        }
        
        $returnRoute = Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl().'?page='. $pageNumber);
        
        return TestTake::redirectToDetail($testTakeUuid, $returnRoute, $pageAction);
    }
}