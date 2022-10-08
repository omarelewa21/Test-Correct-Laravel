<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use tcCore\Http\Traits\Actions\WithPlanButtonFeatures;
use tcCore\Test;

class TestPlanTest extends TestAction
{
    use WithPlanButtonFeatures;

    private $modalName = 'teacher.test-plan-modal';

    public function mount($uuid, $variant = 'icon-button-with-text', $class = '')
    {
        parent::mount($uuid, $variant, $class);
    }

    public function handle()
    {
        $this->planTest();
    }

    protected function getDisabledValue()
    {
        return false;
    }
}
