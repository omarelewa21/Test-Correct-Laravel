<?php

namespace tcCore\Http\Livewire\Actions;

class TestMakePdf extends TestAction
{
    public $testTake;

    public function mount($uuid, $variant = 'icon-button', $class = '', $testTake = null): void
    {
        $this->testTake = $testTake;
        parent::mount($uuid, $variant, $class);
    }

    public function handle()
    {
        $this->emit(
            'openModal',
            'teacher.pdf-download-modal',
            [
                'uuid' => $this->uuid,
                'testTake' => $this->testTake,
            ]
        );
    }

    protected function getDisabledValue(): bool
    {
        return $this->test->isDraft();
    }
}
