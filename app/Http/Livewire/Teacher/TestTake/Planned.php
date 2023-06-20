<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Invigilator;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\User;

class Planned extends TestTakeComponent
{
    public $dropdownData = [];
    public $selected = [];
    public Collection $invigilators;

    public function mount(TestTakeModel $testTake)
    {
        parent::mount($testTake);
        $this->setInvigilators();
    }

    public function refresh()
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
            'test:id,name,uuid,subject_id',
            'test.subject:id,name',
            'scheduledByUser:id,name,name_first,name_suffix',
            'user:id,name,name_first,name_suffix,uuid',
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
            });
    }

    private function setInvigilators(): void
    {
        $this->invigilators = $this->testTake
            ->invigilatorUsers
            ->map(function ($user) {
                $user->displayName = $user->getFullNameWithAbbreviatedFirstName();
                return $user;
            });
    }

    public function removeParticipant(TestParticipant $participant)
    {
        try {
            $participant->delete();
        } catch (\Exception) {
        }
    }

    public function removeInvigilator(User $invigilatorUser): void
    {
        try {
            Invigilator::whereTestTakeId($this->testTake->id)
                ->whereUserId($invigilatorUser->getKey())
                ->delete();
        } catch (\Exception) {
        }
        $this->fillGridData();
        $this->setInvigilators();
    }

    public function canStartTestTake(): bool
    {
        return $this->testTake->time_start->isToday();
    }

    public function startTake()
    {
        
    }
}