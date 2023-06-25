<?php

namespace tcCore\View\Components\partials;

use Illuminate\Support\Facades\Auth;
use tcCore\TestTake;
use tcCore\View\Components\Abstracts\TestTakeInfoLabels;

class AfterTakeInfoLabels extends TestTakeInfoLabels
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(protected TestTake $testTake)
    {
        parent::__construct($testTake);
    }

    protected function showAppIcon(): bool
    {
        return true;
    }

    protected function showWebIcon(): bool
    {
        return true;
    }

    protected function showTestDirectIcon(): bool
    {
        return Auth::user()->guest;
    }

    protected function showRedoIcon(): bool
    {
        return $this->testTake->retake;
    }

    protected function getTooltip(string $iconName): string
    {
        return __('student.icons-tooltip.after-test-take.' . $iconName);
    }
}
