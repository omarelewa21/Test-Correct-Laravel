@extends('layouts.test-take')

@section('gridData')
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.CO-Learning besproken vragen')</span>
        <h6>{{ $this->takenTestData['discussedQuestions'] }} / {{ $this->takenTestData['questionCount'] }}</h6>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.Nagekeken vragen')</span>
        <h6>{{ $this->takenTestData['assessedQuestions'] }} / {{ $this->takenTestData['questionsToAssess'] }}</h6>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('review.in te zien tot')</span>
        <span class="flex items-center gap-2">
            <x-button.text-button size="sm"
                                  class="min-h-0 -mt-1"
                                  wire:click="$emit('openModal', 'teacher.test-take.set-student-review-modal', {testTake: '{{ $this->testTakeUuid }}' });"
            >
                <x-icon.edit />
                <span>{{ $this->showResultsButtonText() }}</span>
            </x-button.text-button>
            <x-input.toggle :disabled="!$this->testTake->show_results"
                            wire:click="$toggle('reviewActive')"
                            :checked="$this->reviewActive"
            />
        </span>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.Resultaten gepubliceerd op')</span>
        @if($this->testTake->results_published)
            <h6>{{ $this->testTake->results_published->format('d-m-Y') }}</h6>
        @else
            <h6>@lang('test-take.Nog niet gepubliceerd')</h6>
        @endif
    </div>
@endsection

@section('cta')
    <div class="flex gap-2 justify-center">
        <x-dynamic-component :component="'button.'. $this->getButtonType('CO-Learning')"
                             wire:click="startCoLearning" class="px-4">
            <x-icon.co-learning />
            <span>@lang('co-learning.co_learning')</span>
        </x-dynamic-component>
        <x-dynamic-component :component="'button.'. $this->getButtonType('Assessment')"
                             wire:click="startAssessment" class="px-4">
            <x-icon.review />
            <span>@lang('assessment.Nakijken')</span>
        </x-dynamic-component>
    </div>
@endsection

@if($this->testTakeStatusId >= \tcCore\TestTakeStatus::STATUS_TAKEN)
    @section('studentNames')
        <div class="flex">
            <x-input.toggle-row-with-title wire:model="showStudentNames"
                                           container-class="!border-0"
            >
                <x-icon.preview class="min-w-[20px]" />
                <span class="min-w-max">@lang('assessment.Studentnamen tonen')</span>
            </x-input.toggle-row-with-title>

        </div>
    @endsection
@endif

@section('action-buttons')
    @if($this->testTake->is_rtti_test_take)
        <x-button.icon class="order-5">
            <x-icon.upload />
        </x-button.icon>
    @endif
    <x-button.icon class="order-5"
                   wire:click="$emit('openModal', 'teacher.test-plan-redo-modal', {testUuid: '{{ $this->testTake->test->uuid }}', testTakeUuid: '{{ $this->testTakeUuid }}' })">
        <x-icon.redo-test />
    </x-button.icon>

    @if($this->testTakeStatusId === \tcCore\TestTakeStatus::STATUS_TAKEN)
        <x-button.icon wire:click="startAssessment" class="order-5">
            <x-icon.review />
        </x-button.icon>

        <x-button.cta wire:click="startCoLearning" class="px-4 order-1">
            <x-icon.co-learning />
            <span>@lang('co-learning.co_learning')</span>
        </x-button.cta>
    @else
        <x-button.icon wire:click="startCoLearning" class="order-5">
            <x-icon.co-learning />
        </x-button.icon>

        <x-button.cta wire:click="startAssessment" class="px-4 order-1">
            <x-icon.review />
            <span>@lang('assessment.Nakijken')</span>
        </x-button.cta>
    @endif
@endsection

@section('waitingRoom')
    <div @class(['flex flex-col gap-4', 'hidden' => !$this->showWaitingRoom])>
        @if($this->showWaitingRoom)
            <h2>@lang('test-take.Wachtkamer')</h2>
            <div class="flex flex-col pt-4 pb-10 px-10 bg-white rounded-10 content-section relative"
                 x-data="{plannedTab: 'students'}"
                 x-cloak
            >
                <x-menu.tab.container :withTileEvents="false" max-width-class="">
                    <x-menu.tab.item tab="students" menu="plannedTab" selid="test-take-overview-tab-taken"
                                     class="-ml-2">
                        @lang('test-take.Studenten')
                    </x-menu.tab.item>
                    <x-menu.tab.item tab="invigilators" menu="plannedTab" selid="test-take-overview-tab-norm">
                        @lang('student.invigilators')
                    </x-menu.tab.item>
                </x-menu.tab.container>

                <span class="absolute right-10 top-6 z-1">
                    <x-tooltip>@lang('test-take.waiting-room-tooltip')</x-tooltip>
                </span>

                <div x-show="plannedTab === 'students'"
                     class="flex flex-col w-full pt-5"
                >
                    <div class="flex w-full relative flex-wrap gap-2">
                        @if($this->initialized)
                            @forelse($this->participants as $participant)
                                <div @class([
                            'filter-pill px-4 gap-2 h-10 transition-opacity',
                            'disabled' => !$participant->present,
                            'enabled' => $participant->present
                            ])
                                     wire:key="participant-{{ $participant->uuid }}-@js($participant->present)"
                                >
                                    <span>{{ $participant->name }}</span>
                                </div>
                            @empty
                                <span>@lang('test-take.Geen studenten beschikbaar')</span>
                            @endforelse
                        @else
                            <div class="flex w-full h-full items-center justify-center">
                                <x-icon.loading-large class="animate-spin" />
                            </div>
                        @endif
                    </div>
                </div>
                <div x-show="plannedTab === 'invigilators'"
                     class="flex flex-col w-full pt-5"
                >
                    <div class="flex w-full relative flex-wrap gap-2">
                        @forelse($this->invigilatorUsers as $invigilatorUser)
                            <div class="filter-pill px-4 gap-2 h-10 enabled transition-opacity"
                                 wire:key="invigilator-{{ $invigilatorUser->uuid }}"
                            >
                                <span>{{ $invigilatorUser->getFullNameWithAbbreviatedFirstName() }}</span>
                            </div>
                        @empty
                            <span>@lang('test-take.Geen surveillanten beschikbaar')</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@if($this->testTakeStatusId >= \tcCore\TestTakeStatus::STATUS_DISCUSSED)
    @section('results')
        <div class="flex flex-col gap-4">
            <h2>@lang('header.Resultaten')</h2>
            <div class="flex flex-col pt-4 pb-10 px-10 bg-white rounded-10 content-section" x-cloak>
                <h4>@lang('test-take.Resultaten overzicht')</h4>

                <div class="divider mt-3 mb-2.5"></div>

                <div class="results-grid grid grid-cols-6 ">
                    <div class="bold">Student</div>
                    <div class="bold">Besproken</div>
                    <div class="bold">Nagekeken</div>
                    <div class="bold">Score/Max</div>
                    <div class="bold">Discr.</div>
                    <div class="bold">Extra informatie</div>

                    <div class="col-span-6 h-[3px] bg-sysbase my-2"></div>

                    @foreach($this->participants as $participant)
                        <div class="contents group/row hover:text-primary">
                            <div class="flex items-center group-hover/row:bg-offwhite pr-1 col-start-1 h-15">{{ $participant->name }}</div>
                            <div class="flex items-center group-hover/row:bg-offwhite px-1">0/0</div>
                            <div class="flex items-center group-hover/row:bg-offwhite px-1">0/0</div>
                            <div class="flex items-center group-hover/row:bg-offwhite px-1">0/0</div>
                            <div class="flex items-center group-hover/row:bg-offwhite px-1">0</div>
                            <div class="flex items-center group-hover/row:bg-offwhite pl-1">
                                <div class="flex items-center">
                                    <x-tooltip class="w-[40px] h-[30px]" :icon-height="true" :icon-width="true">
                                        <x-slot:idleIcon>
                                                    <span class="flex items-center" x-show="!tooltip" x-cloak>
                                                        <x-icon.profile />
                                                        <x-icon.i-letter />
                                                    </span>
                                        </x-slot:idleIcon>
                                        lekker dan piek
                                    </x-tooltip>
                                    <x-icon.web />
                                    <x-icon.time-dispensation />
                                    <x-icon.speech-bubble />
                                    <x-icon.notepad />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-col">

                </div>

                {{--<table class="min-w-full">
                    <thead class="border-b-3 border-system-base z-[2]">
                    <tr>
                        <x-table.heading class="!p-0" width="220px">Student</x-table.heading>
                        <x-table.heading class="!p-0" width="90px">Besproken</x-table.heading>
                        <x-table.heading class="!p-0" width="90px">Nagekeken</x-table.heading>
                        <x-table.heading class="!p-0" width="90px">Score/Max</x-table.heading>
                        <x-table.heading class="!p-0" width="45px">Discr.</x-table.heading>
                        <x-table.heading class="!p-0">Extra informatie</x-table.heading>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($this->participants as $participant)
                        <tr class="new-table-row h-15 group/row border-b border-secondary">
                            <td>{{ $participant->name }}</td>
                            <td>0/0</td>
                            <td>0/0</td>
                            <td>0/0</td>
                            <td>0</td>
                            <td>
                                <div class="flex z-10">
                                    <x-tooltip class="w-[40px] h-[30px]" :icon-height="true" :icon-width="true">
                                        <x-slot:idleIcon>
                                                <span class="flex items-center" x-show="!tooltip" x-cloak>
                                                    <x-icon.profile />
                                                    <x-icon.i-letter />
                                                </span>
                                        </x-slot:idleIcon>
                                        lekker dan piek
                                    </x-tooltip>
                                    <x-icon.web />
                                    <x-icon.time-dispensation />
                                    <x-icon.speech-bubble />
                                    <x-icon.notepad />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>--}}
            </div>
        </div>
    @endsection
@endif