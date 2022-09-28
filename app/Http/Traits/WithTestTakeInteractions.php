<?php


namespace tcCore\Http\Traits;

use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\TestTake;

trait WithTestTakeInteractions
{
    public function openTestTakeDetail($testTakeUuid)
    {
        $returnRoute = Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl());
        return TestTake::redirectToDetail($testTakeUuid, $returnRoute);
    }
}