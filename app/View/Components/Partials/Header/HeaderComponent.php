<?php

namespace tcCore\View\Components\Partials\Header;

use Illuminate\View\Component;
use tcCore\Http\Helpers\GlobalStateHelper;

abstract class HeaderComponent extends Component
{
    public readonly bool $hasActiveMaintenance;
    public readonly bool $isOnDeploymentTesting;
    public string $backButtonTitle;

    public function __construct() {
        $globalStateHelper = GlobalStateHelper::getInstance();
        $this->hasActiveMaintenance = $globalStateHelper->hasActiveMaintenance();
        $this->isOnDeploymentTesting = $globalStateHelper->isOnDeploymentTesting();
        $this->backButtonTitle = __('test-take.Terug');
    }
}