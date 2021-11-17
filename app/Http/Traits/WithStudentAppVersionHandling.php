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
        AppVersionDetector::handleHeaderCheck();
    }

    public function participantAppCheck($participant)
    {
        $appStatus = AppVersionDetector::isVersionAllowed();

        $this->needsApp = !!(!$participant->canUseBrowserTesting());
        $this->meetsAppRequirement = !!($appStatus != AllowedAppType::NOTALLOWED);
        $this->appNeedsUpdate = !!($appStatus === AllowedAppType::NEEDSUPDATE);

        if ($this->appNeedsUpdate) {
            $this->appNeedsUpdateDeadline = AppVersionDetector::needsUpdateDeadline();
        }
    }
}