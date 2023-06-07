<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use tcCore\Http\Livewire\TCComponent;
use tcCore\TestTake as TestTakeModel;

abstract class TestTake extends TCComponent
{
    public string $testTakeUuid;
    protected TestTakeModel $testTake;

    public function mount(TestTakeModel $testTake)
    {
        $this->testTakeUuid = $testTake->uuid;
        $this->testTake = $testTake;
    }

    public function hydrate()
    {
        $this->testTake = TestTakeModel::whereUuid($this->testTakeUuid)->first();
    }

    public function render()
    {
        $template= class_basename(get_called_class());
        return view('livewire.teacher.test-take.' . $template)->layout('layouts.app-teacher');
    }
}
