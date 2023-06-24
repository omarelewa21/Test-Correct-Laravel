<?php

namespace tcCore\View\Components\partials;

use Illuminate\Support\Facades\Lang;
use tcCore\TestTake;
use tcCore\View\Components\Abstracts\TestTakeInfoLabels;

class BeforeTakeInfoLabels extends TestTakeInfoLabels
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    protected function __construct(private TestTake $testTake)
    {
        parent::__construct();
    }

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
        return $this->testTake->retake;
    }

    protected function getTooltip(string $iconName): string
    {
        return match ($iconName) {
            'app'           => @Lang::get('student.icons-tooltip.before-test-take.app'),
            'web'           => @Lang::get('student.icons-tooltip.before-test-take.web'),
            'test-direct'   => @Lang::get('student.icons-tooltip.before-test-take.test-direct'),
            'redo'          => @Lang::get('student.icons-tooltip.before-test-take.redo'),
            default => '',
        };
    }
}
