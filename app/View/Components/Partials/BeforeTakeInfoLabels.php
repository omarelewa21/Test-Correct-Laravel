<?php

namespace tcCore\View\Components\partials;

use tcCore\View\Components\Abstracts\TestTakeInfoLabels;

class BeforeTakeInfoLabels extends TestTakeInfoLabels
{
    protected function showAppIcon(): bool
    {
        return true;
    }

    protected function showWebIcon(): bool
    {
        return $this->testTake->allow_inbrowser_testing;
    }

    protected function showTestDirectIcon(): bool
    {
        return $this->testTake->guest_accounts;
    }

    protected function showRedoIcon(): bool
    {
        return $this->testTake->retake ?? false;
    }

    protected function getTooltip(string $iconName): string
    {
        return __('student.icons-tooltip.before-test-take.' . $iconName);
    }
}
