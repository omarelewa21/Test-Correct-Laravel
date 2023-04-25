<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Traits\Modal\TestActions;
use tcCore\Http\Traits\WithUpdatingHandling;
use tcCore\Test;

abstract class TestModal extends TCModalComponent
{
    use TestActions;
    use WithUpdatingHandling;

    public bool $forceClose = true;

    public $request = [];

    public function mount()
    {
        $this->setAllowedProperties();

        $this->setRequestPropertyDefaults();
    }

    public function submit()
    {
        $this->validate();

        $test = $this->performModalAction();

        $this->finishSubmitting($test);
    }

    /**
     * @return void
     */
    private function setAllowedProperties(): void
    {
        $this->allowedSubjects = $this->getAllowedSubjects();
        $this->allowedTestKinds = $this->getAllowedTestKinds();
        $this->allowedPeriods = $this->getAllowedPeriods();
        $this->allowedEductionLevels = $this->getAllowedEducationLevels();
    }

    abstract protected function setRequestPropertyDefaults(): void;
    abstract protected function performModalAction(): Test;
    abstract protected function finishSubmitting(Test $test): void;
}
