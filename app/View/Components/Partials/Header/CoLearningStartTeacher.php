<?php

namespace tcCore\View\Components\Partials\Header;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Http\Helpers\GlobalStateHelper;

class CoLearningStartTeacher extends Component
{
    public readonly bool $hasActiveMaintenance;
    public readonly bool $isOnDeploymentTesting;

    public function __construct(
        public readonly string  $testName,
    )
    {
        $globalStateHelper = GlobalStateHelper::getInstance();
        $this->hasActiveMaintenance = $globalStateHelper->hasActiveMaintenance();
        $this->isOnDeploymentTesting = $globalStateHelper->isOnDeploymentTesting();
    }

    public function render()
    {
        return view('components.partials.header.co-learning-start-teacher');
    }
}
