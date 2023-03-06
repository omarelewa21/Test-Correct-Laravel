<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\TestTake;

class Assessment extends Component implements CollapsableHeader
{
    public bool $headerCollapsed = false;
    public bool $skipDiscrepancies = false;
    public bool $skippedCoLearning = false;

    public string $testName;
    public string $testTakeUuid;

    protected $queryString = ['referrer' => ['except' => '']];
    public string $referrer = '';

    public function mount(TestTake $testTake)
    {
        $this->testName = $testTake->test->name;
        $this->testTakeUuid = $testTake->uuid;
        $this->skippedCoLearning = !$testTake->skipped_discussion;
    }

    public function render()
    {
        return view('livewire.teacher.assessment')
            ->layout('layouts.assessment');
    }

    public function handleHeaderCollapse($args)
    {
        $this->headerCollapsed = true;
        return true;
    }

    public function redirectBack()
    {
        if (blank($this->referrer) || $this->referrer === 'cake') {
            return CakeRedirectHelper::redirectToCake('test_takes.view', $this->testTakeUuid);
        }

        return redirect()->route($this->referrer, 'norm');
    }
}