<?php


namespace tcCore\Http\Traits;

use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\TestTake;
use Illuminate\Http\Request;

trait WithTestTakeInteractions
{
    public function openTestTakeDetail($testTakeUuid, $pageNumber)
    {
        if (TestTake::whereUuid($testTakeUuid)->first()->archived) {
            $this->dispatchBrowserEvent('notify', ['message' => __('test_take.unarchive_test_take_first'), 'type' => 'error']);
            return;
        }
// dd($pageNumber);
        $returnRoute = Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl(),'?page=', $pageNumber);
        return TestTake::redirectToDetail($testTakeUuid, $returnRoute);
    }
}