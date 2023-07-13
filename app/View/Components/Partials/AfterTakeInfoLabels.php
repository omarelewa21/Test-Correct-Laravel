<?php

namespace tcCore\View\Components\Partials;

use Illuminate\Support\Facades\Auth;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\View\Components\Abstracts\TestTakeInfoLabels;

class AfterTakeInfoLabels extends TestTakeInfoLabels
{
    protected TestParticipant $testParticipant;

    protected function __construct(protected TestTake $testTake)
    {
        $this->testParticipant = $testTake->testParticipants()->where('user_id', Auth::id())->first();

        parent::__construct($testTake);
    }

    protected function showAppIcon(): bool
    {
        return $this->testParticipant->testTakeEvents()->whereJsonContains('metadata->device', 'app')->exists();
    }

    protected function showWebIcon(): bool
    {
        return $this->testParticipant->testTakeEvents()->whereJsonContains('metadata->device', 'browser')->exists();
    }

    protected function showTestDirectIcon(): bool
    {
        return Auth::user()->guest;
    }

    protected function showRedoIcon(): bool
    {
        return $this->testTake->retake ?? false;
    }

    protected function getTooltip(string $iconName): string
    {
        return __('student.icons-tooltip.after-test-take.' . $iconName);
    }
}
