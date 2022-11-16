<?php

namespace tcCore\Http\Livewire\Actions;

use Auth;
use tcCore\Http\Traits\Actions\WithPlanButtonFeatures;
use tcCore\Test;

class TestMakePublished extends TestAction
{
    public function mount($uuid, $variant = 'icon-button-with-text', $class = '')
    {
        parent::mount($uuid, $variant, $class);
    }

    public function handle()
    {
        Test::findByUuid($this->uuid)->publish()->save();
        $this->emit('test-updated');
    }

    protected function getDisabledValue()
    {
        return $this->test->isPublished();
    }
}
