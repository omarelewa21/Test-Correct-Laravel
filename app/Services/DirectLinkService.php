<?php

namespace tcCore\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Ramsey\Uuid\Uuid;
use tcCore\RelationQuestion;
use tcCore\TemporaryLogin;
use tcCore\TestTake;
use tcCore\User;

class DirectLinkService
{
    private readonly string $testTakeUuid;
    private ?TestTake $testTake = null;
    private ?User $user = null;

    private function __construct(string $testTakeUuid)
    {
        $this->testTakeUuid = $testTakeUuid;
    }

    public static function handle(string $testTakeUuid): Redirector|RedirectResponse
    {
        $service = new self($testTakeUuid);

        return $service->redirectIfGuest()
            ?? $service->redirectIfNoValidTestTake()
            ?? $service->redirectIfStudent()
            ?? $service->redirectIfTestTakeOwner()
            ?? $service->redirectIfInvigilator()
            ?? $service->redirectDefault();
    }

    /* Scenario's */
    private function redirectIfGuest(): null|RedirectResponse|Redirector
    {
        if (auth()->check()) {
            $this->user = auth()->user();
            return null;
        }

        return redirect()->route('auth.login', ['directlink' => $this->testTakeUuid]);
    }

    private function redirectIfNoValidTestTake(): null|RedirectResponse|Redirector
    {
        if (Uuid::isValid($this->testTakeUuid)) {
            $this->testTake = TestTake::whereUuid($this->testTakeUuid)
                ->with(['test:id,name', 'user'])
                ->first();

            if ($this->testTake) {
                return null;
            }
        }

        return $this->redirectDefault();
    }

    private function redirectIfStudent(): null|RedirectResponse|Redirector
    {
        if (!$this->user->isA('Student')) {
            return null;
        }

        if ($this->blockedByRelationQuestion()) {
            return redirect()->route('student.dashboard');
        }

        return redirect()->route('student.waiting-room', ['take' => $this->testTakeUuid]);
    }

    private function redirectIfTestTakeOwner(): null|RedirectResponse|Redirector
    {
        if (!$this->user->isA('teacher')) {
            return null;
        }

        if ($this->testTake->user->isNot($this->user)) {
            return null;
        }

        if ($this->blockedByRelationQuestion()) {
            return $this->redirectTeacherWithRelationQuestionBlock();
        }

        return $this->redirectTakeOwner();
    }

    private function redirectIfInvigilator(): null|RedirectResponse|Redirector
    {
        if (!$this->testTake->isInvigilator($this->user)) {
            return null;
        }

        return $this->redirectTakeInvigilator();
    }

    private function redirectDefault(): RedirectResponse|Redirector
    {
        return $this->redirectToCorrectTakePage(notification: __('teacher.test_not_found'));
    }

    /* Scenario handling */
    private function redirectTakeOwner(): RedirectResponse
    {
        return $this->redirectToCorrectTakePage(url: $this->getRedirectUrlForOwner());
    }

    private function getRedirectUrlForOwner(): string
    {
        if ($this->testTake->hasFinishedTakingTest()) {
            return sprintf("test_takes/view/%s", $this->testTakeUuid);
        }

        if ($this->testTake->isAssignmentType()) {
            return sprintf("test_takes/assignment_open_teacher/%s", $this->testTakeUuid);
        }

        if ($this->testTake->hasStatusTakingTest()) {
            return "test_takes/surveillance";
        }

        return sprintf("test_takes/view/%s", $this->testTakeUuid);
    }

    private function redirectTakeInvigilator(): RedirectResponse
    {
        if ($notification = $this->getNotificationForInvigilator()) {
            return $this->redirectToCorrectTakePage(notification: $notification);
        }

        return $this->redirectTakeOwner();
    }

    private function getNotificationForInvigilator(): ?string
    {
        if ($this->testTake->hasNotFinishedTakingTest()) {
            return null;
        }

        return __(
            'teacher.take_not_accessible_toast_for_invigilator',
            ['testName' => $this->testTake->test->name]
        );
    }

    private function redirectToCorrectTakePage($notification = null, $url = null): RedirectResponse
    {
        $options = $notification
            ? TemporaryLogin::buildValidOptionObject('notification', [$notification => 'info'])
            : TemporaryLogin::buildValidOptionObject('page', $url);

        return $this->user->redirectToCakeWithTemporaryLogin($options);
    }

    private function blockedByRelationQuestion(): bool
    {
        if (settings()->canUseRelationQuestion($this->user)) {
            return false;
        }

        return $this->testTake->test->containsSpecificQuestionTypes(RelationQuestion::class);
    }

    private function redirectTeacherWithRelationQuestionBlock(): RedirectResponse
    {
        return $this->testTake->hasFinishedTakingTest()
            ? redirect()->route('teacher.test-takes', ['stage' => 'taken', 'rqw' => 'true'])
            : redirect()->route('teacher.tests', ['rqw' => 'true']);
    }
}