<?php

namespace tcCore\View\Components\Partials\Header;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Http\Helpers\GlobalStateHelper;

class CoLearningTeacher extends Component
{
    public readonly string $discussionTypeTranslation;
    public readonly bool $hasActiveMaintenance;
    public readonly bool $isOnDeploymentTesting;

    public function __construct(
        public readonly string  $testName,
        public readonly bool    $atLastQuestion,
        private readonly string $discussionType,
    )
    {
        $this->discussionTypeTranslation = $this->discussionType === 'OPEN_ONLY'
            ? Str::upper(__('co-learning.open_questions'))
            : Str::upper(__('co-learning.all_questions'));

        $globalStateHelper = GlobalStateHelper::getInstance();
        $this->hasActiveMaintenance = $globalStateHelper->hasActiveMaintenance();
        $this->isOnDeploymentTesting = $globalStateHelper->isOnDeploymentTesting();
    }

    public function render()
    {
        return view('components.partials.header.co-learning-teacher');
    }
}
