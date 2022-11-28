<?php

namespace tcCore\Http\Livewire\Actions;

class TestMakePublished extends TestAction
{
    public function mount($uuid, $variant = 'icon-button-with-text', $class = '')
    {
        parent::mount($uuid, $variant, $class);
    }

    public function handle()
    {
        $this->emit('openModal', 'teacher.publish-test-modal', ['testUuid' => $this->uuid]);
    }

    protected function getDisabledValue()
    {
        return $this->test->isPublished();
    }
}
