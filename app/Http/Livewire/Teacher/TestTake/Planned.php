<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Http\Livewire\Teacher\TestTakeWarningModal;
use tcCore\Invigilator;
use tcCore\Log;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\User;

class Planned extends TestTakeComponent
{
    public $dropdownData = [];
    public $selected = [];
    public Collection $invigilatorUsers;

    public function mount(TestTakeModel $testTake)
    {
        parent::mount($testTake);
        $this->setInvigilators();
    }

    public function refresh(): void
    {
        $this->fillGridData();
        $this->setStudentData();
        $this->setInvigilators();
    }

    public function redirectToOverview()
    {
        return CakeRedirectHelper::redirectToCake('planned.my_tests');
    }

    public function fillGridData()
    {
        $this->testTake->load([
            'test:id,name,uuid,subject_id,test_kind_id',
            'test.subject:id,name',
            'scheduledByUser:id,name,name_first,name_suffix',
            'user:id,name,name_first,name_suffix,uuid',
            'invigilators:test_take_id,user_id,uuid',
            'invigilatorUsers:id,name,name_first,name_suffix,uuid',

        ]);
        $schoolClasses = $this->testTake->schoolClasses()->get('name');
        $this->gridData = [
            [
                'title' => __('student.subject'),
                'data'  => $this->testTake->test->subject->name,
            ],
            [
                'title' => __('test-take.Afname gepland op'),
                'data'  => $this->testTake->time_start->format('d-m-Y'),
            ],
            [
                'title' => __('test-take.Gepland door'),
                'data'  => $this->testTake->scheduledByUser?->getFullNameWithAbbreviatedFirstName(),
            ],
            [
                'title' => trans_choice('test-take.Klas', $schoolClasses->count()),
                'data'  => $schoolClasses
                    ->map(fn($class) => $class->name)
                    ->join(', ', sprintf(" %s ", __('test-take.and'))),
            ],
            [
                'title' => __('general.Docent'),
                'data'  => $this->testTake->user->getFullNameWithAbbreviatedFirstName(),
            ],
            [
                'title' => trans_choice('test-take.Surveillant', $this->testTake->invigilatorUsers->count()),
                'data'  => $this->testTake->invigilatorUsers
                    ->map(fn($user) => $user->getFullNameWithAbbreviatedFirstName())
                    ->join(', ', sprintf(" %s ", __('test-take.and'))),
            ],
            [
                'title' => __('teacher.Weging'),
                'data'  => $this->testTake->weight,
            ],
            [
                'title' => __('teacher.type'),
                'data'  => Blade::renderComponent(
                    new AnonymousComponent(
                        'components.partials.test-take-type-label',
                        ['type' => $this->testTake->retake]
                    )
                ),
            ],
        ];

        if ($this->testTake->test->test_kind_id === TestKind::ASSIGNMENT_TYPE) {
            array_splice(
                $this->gridData,
                1,
                0,

                [
                    'title' => __('test-take.Beschikbaar tot'),
                    'data'  => $this->testTake->time_end->format('d-m-Y'),
                ]

            );
        }
    }

    protected function setStudentData(): void
    {
        $this->testTake->load([
            'testParticipants',
            'testParticipants.user:id,name,name_first,name_suffix,uuid'
        ]);

        $this->participants = $this->testTake
            ->testParticipants
            ->each(function ($participant) {
                $participant->name = $participant->user->name_full;
                $participant->present = $this->activeParticipantUuids->contains($participant->user->uuid);
                $participant->user->setAppends([]); /* Disables unnecessary append queries */
            });
    }

    private function setInvigilators(): void
    {
        $this->invigilatorUsers = $this->testTake
            ->load([
                'invigilators:test_take_id,user_id,uuid',
                'invigilatorUsers:id,name,name_first,name_suffix,uuid',
            ])
            ->invigilatorUsers
            ->map(function ($user) {
                $invigilator = $this->testTake
                    ->invigilators
                    ->first(fn($invigilator) => $invigilator->user_id === $user->id);
                $user->invigilator_uuid = $invigilator->uuid;
                $user->displayName = $user->getFullNameWithAbbreviatedFirstName();

                $user->setAppends([]);
                $invigilator->setAppends([]);

                return $user;
            });
    }

    public function removeParticipant($participantUuid): void
    {
        try {
            TestParticipant::whereUuid($participantUuid)->delete();
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function removeInvigilator($invigilatorUuid): void
    {
        try {
            Invigilator::whereUuid($invigilatorUuid)->delete();
        } catch (\Exception $e) {
            Log::error($e);
        }
        $this->fillGridData();
        $this->setInvigilators();
    }

    public function canStartTestTake(): bool
    {
        return $this->testTake->time_start->isToday();
    }

    public function startTake(): void
    {
        $warnings = collect([
            'browser_testing' => $this->testTake->allow_inbrowser_testing,
            'guest_accounts' => $this->testTake->guest_accounts,
            'participants_incomplete' => $this->participants->count() !== $this->activeParticipantUuids->count(),
        ])->filter();

        if ($warnings->isNotEmpty()) {
            $this->emit(
                'openModal',
                'teacher.test-take-warning-modal',
                ['testTake' => $this->testTakeUuid, 'warnings' => $warnings]
            );
            return;
        }

        $this->testTake->startTake();
        CakeRedirectHelper::redirectToCake('planned.surveillance');
    }

    public function setDataPropertiesForTemplate(): void
    {
        $this->setStudentData();
        $this->setInvigilators();
    }
}