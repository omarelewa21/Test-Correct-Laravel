<?php


namespace tcCore\Http\Traits;

use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Livewire\Teacher\TestTakeOverview;
use tcCore\TestTake;
use Illuminate\Http\Request;


trait WithTestTakeInteractions
{
    public function openTestTakeDetail($testTakeUuid)
    {
        $pageNumber = session()->get(TestTakeOverview::PAGE_NUMBER_SESSION_KEY);
        
        if (TestTake::whereUuid($testTakeUuid)->first()->archived) {
            $this->dispatchBrowserEvent('notify', ['message' => __('test_take.unarchive_test_take_first'), 'type' => 'error']);
            return;
        }
        
        $returnRoute = Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl().'?page='. $pageNumber);
        
        return TestTake::redirectToDetail($testTakeUuid, $returnRoute);
    }
}