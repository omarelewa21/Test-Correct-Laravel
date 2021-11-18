<?php

namespace tcCore\Http\Traits;

use tcCore\Http\Helpers\AllowedAppType;
use tcCore\Http\Helpers\AppVersionDetector;

trait WithStudentAppVersionHandling
{
    public $meetsAppRequirement = true;
    public $needsApp;
    public $appNeedsUpdate;
    public $appNeedsUpdateDeadline;

    public function mountWithStudentAppVersionHandling()
    {
//        AppVersionDetector::handleHeaderCheck();
    }

}