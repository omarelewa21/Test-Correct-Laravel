<?php

namespace tcCore\Http\Livewire\Actions;

use tcCore\Http\Traits\Actions\WithPlanButtonFeatures;
use tcCore\Test;

class TestQuickTake extends TestAction
{
    use WithPlanButtonFeatures;

    private $modalName = 'teacher.test-quick-take-modal';

    public bool $disabled;

    public function mount($uuid, $variant = 'icon-button', $class)
    {
        parent::mount($uuid, $variant, $class);

        $this->disabled = Test::select(['id','test_kind_id'])->whereUuid($uuid)->first()->isAssignment();
    }
}
