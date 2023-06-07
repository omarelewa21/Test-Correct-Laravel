<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\TestTake as TestTakeModel;

abstract class TestTake extends TCComponent
{
    use WithReturnHandling;

    public string $testTakeUuid;
    protected TestTakeModel $testTake;
    public array $gridData = [];

    public function mount(TestTakeModel $testTake)
    {
        $this->testTakeUuid = $testTake->uuid;
        $this->setTestTake($testTake);
        $this->fillGridData();
    }

    public function hydrate()
    {
        $this->setTestTake();
    }

    public function render()
    {
        $template = class_basename(get_called_class());
        return view('livewire.teacher.test-take.' . $template)->layout('layouts.app-teacher');
    }

    abstract public function redirectToOverview();

    public function back()
    {
        return $this->redirectUsingReferrer();
    }

    private function setTestTake(TestTakeModel $testTake = null): void
    {
        $this->testTake = $testTake ?? TestTakeModel::whereUuid($this->testTakeUuid)->first();
    }
}
