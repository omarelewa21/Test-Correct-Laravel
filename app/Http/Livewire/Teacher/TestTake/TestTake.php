<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\AnonymousComponent;
use tcCore\Events\TestTakePresenceEvent;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;

abstract class TestTake extends TCComponent
{
    use WithReturnHandling;

    public string $testTakeUuid;
    protected TestTakeModel $testTake;
    public Collection $participants;
    public Collection $activeParticipantUuids;

    public array $gridData = [];
    public bool $initialized = false;

    protected function getListeners(): array
    {
        return $this->getPusherListeners() + ['refresh' => 'refresh'];
    }

    public function mount(TestTakeModel $testTake): void
    {
        // we need to add a guard to determine if you are allowed to see this test take
        // with the help of the isAllowedToView method of the test take model
        // as per TCP-3479 the exam coordinator is allowed to view a test take once rated or when planned
        Gate::authorize('isAllowedToViewTestTake',[$testTake]);

        $this->testTakeUuid = $testTake->uuid;
        $this->setTestTake($testTake);
        $this->fillGridData();
        $this->participants = collect();
        $this->activeParticipantUuids = collect();
        $this->setInvigilators();
    }

    public function hydrate()
    {
        $this->setTestTake();
    }

    public function render()
    {
        if ($this->initialized) {
            $this->setDataPropertiesForTemplate();
        }
        $template = str(class_basename(get_called_class()))->lower();
        return view('livewire.teacher.test-take.' . $template)->layout('layouts.app-teacher');
    }

    abstract public function refresh();

    abstract public function redirectToOverview();

    abstract public function breadcrumbTitle(): string;

    public function back()
    {
        return $this->redirectUsingReferrer();
    }

    private function setTestTake(TestTakeModel $testTake = null): void
    {
        $this->testTake = $testTake ?? TestTakeModel::whereUuid($this->testTakeUuid)->first();
    }

    public function initializingPresenceChannel($event): void
    {
        $this->handlePresenceEventUpdate(collect($event)->where('student', true)->pluck('uuid'));
    }

    public function joiningPresenceChannel($event): void
    {
        $this->handlePresenceEventUpdate($this->activeParticipantUuids->push($event['uuid']));
    }

    public function leavingPresenceChannel($event): void
    {
        $this->handlePresenceEventUpdate(collect($event)->where('student', true)->pluck('uuid'));
    }

    private function handlePresenceEventUpdate(Collection $presentUserUuids): void
    {
        $this->initialized = true;
        $this->activeParticipantUuids = $presentUserUuids;
        $this->setStudentData();
    }

    protected function setStudentData(): void
    {
        $this->testTake->loadMissing([
            'testParticipants',
            'testParticipants.user' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'name_first',
                    'name_suffix',
                    'uuid',
                    'time_dispensation',
                    'text2speech'
                )->withTrashed();
            }
        ]);

        $this->participants = $this->testTake
            ->testParticipants
            ->each(function ($participant) {
                $participant->name = html_entity_decode($participant->user->name_full);
                $participant->present = $this->activeParticipantUuids->contains($participant->user->uuid);
                $participant->user->setAppends([]); /* Disables unnecessary append queries */
            });
    }

    protected function setInvigilators(): void
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

    protected function setDataPropertiesForTemplate(): void
    {
        $this->setStudentData();
        $this->setInvigilators();
    }

    protected function fillGridData(): void
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
                2,
                0,
                [
                    [
                        'title' => __('test-take.Beschikbaar tot'),
                        'data'  => $this->testTake->time_end?->format('d-m-Y') ?? '-',
                    ]
                ]

            );
        }
    }

    protected function getPusherListeners(): array
    {
        return [
            TestTakePresenceEvent::channelHereSignature($this->testTake->uuid) => 'initializingPresenceChannel',
            TestTakePresenceEvent::channelJoiningSignature($this->testTake->uuid) => 'joiningPresenceChannel',
            TestTakePresenceEvent::channelLeavingSignature($this->testTake->uuid) => 'leavingPresenceChannel',
        ];
    }

}
