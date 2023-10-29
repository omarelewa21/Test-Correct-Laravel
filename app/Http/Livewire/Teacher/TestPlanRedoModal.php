<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\TestTakeStatus;

class TestPlanRedoModal extends TestPlanModal
{
    public \tcCore\TestTake $testTake;

    public function mount($testUuid, $testTakeUuid = null): void
    {
        $this->testTake = \tcCore\TestTake::whereUuid($testTakeUuid)->firstOrFail();
        parent::mount($testUuid);
        $this->request['period_id'] = $this->testTake->period_id;
        $this->request['weight'] = $this->testTake->weight;
        $this->request['retake'] = true;
    }

    public function render()
    {
        return view('livewire.teacher.test-plan-redo-modal');
    }

    protected function setLabels(): void
    {
        $this->labels = [
            'title' => __('test-take.Inhaaltoets inplannen'),
            'date'  => __('test-take.Inhaaldatum'),
            'cta'   => __('test-take.Inhaaltoets inplannen')
        ];
    }

    public function planNext()
    {
        parent::planNext();
    }

    public function getSchoolClassesProperty(): array
    {
        $classes = $this->testTake->schoolClasses()->get();
        $userIdsThatNeedRedo = $this->testTake
            ->testParticipants()
            ->leftJoin('users', 'users.id', '=', 'test_participants.user_id')
            ->where('test_participants.test_take_status_id', TestTakeStatus::STATUS_TEST_NOT_TAKEN)
            ->get('users.uuid')
            ->map(fn($participant) => $participant->uuid);

        return $this->buildChoicesArrayWithClasses(
            $classes,
            fn($studentUser) => $userIdsThatNeedRedo->contains($studentUser->uuid)
        );
    }
}
