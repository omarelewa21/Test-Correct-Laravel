<?php


namespace tcCore\Http\Traits;

use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\TestTake;

trait WithTestTakeInteractions
{
    public function openTestTakeDetail($testTakeUuid)
    {
        if (TestTake::whereUuid($testTakeUuid)->first()->archived) {
            $this->dispatchBrowserEvent('notify', ['message' => __('test_take.unarchive_test_take_first'), 'type' => 'error']);
            return;
        }
        $returnRoute = Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl());
        return TestTake::redirectToDetail($testTakeUuid, $returnRoute);
    }
}