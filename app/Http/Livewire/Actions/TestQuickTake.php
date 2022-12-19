<?php

namespace tcCore\Http\Livewire\Actions;

use Auth;
use tcCore\Http\Traits\Actions\WithPlanButtonFeatures;

class TestQuickTake extends TestAction
{
    use WithPlanButtonFeatures;

    private $modalName = 'teacher.test-quick-take-modal';

    public function mount($uuid, $variant = 'icon-button', $class = '')
    {
        parent::mount($uuid, $variant, $class);
    }

    public function handle()
    {
        $this->planTest();
    }

    protected function getDisabledValue()
    {
        return $this->test->isAssignment() || Auth::user()->isValidExamCoordinator() || !$this->test->canPlan(Auth::user()) || Auth::user()->isToetsenbakker();
    }
}
