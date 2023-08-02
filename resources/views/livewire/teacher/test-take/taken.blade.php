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

@if($this->testTakeStatusId >= \tcCore\TestTakeStatus::STATUS_DISCUSSED)
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

    @if(in_array($this->testTakeStatusId,[\tcCore\TestTakeStatus::STATUS_TAKEN,\tcCore\TestTakeStatus::STATUS_DISCUSSING]))
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
        <div class="flex flex-col gap-4">
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
        <x-button.cta wire:click="startCoLearning"
                      class="justify-center"
                      size="md"
        >
            <x-icon.co-learning />
            <span>@lang('test-take.CO-Learning starten')</span>
        </x-button.cta>
    </div>
@endsection

@if($this->testTakeStatusId >= \tcCore\TestTakeStatus::STATUS_DISCUSSED)
    @section('results')
        <div class="flex flex-col gap-4">
            <h2>@lang('header.Resultaten')</h2>
            <div class="flex flex-col pt-4 pb-10 px-10 bg-white rounded-10 content-section" x-cloak>
                <h4>@lang('test-take.Resultaten overzicht')</h4>

                <div class="divider mt-3 mb-2.5"></div>

                <div class="results-grid grid -mx-5 relative"
                     x-data="{rowHover: null, shadow: null}"
                     x-init="
                     shadow = $refs.shadowBox
                     $watch('rowHover', value => {
                        if(value !== null) {
                            shadow.style.top = $root.querySelector(`[data-row='${value}'] .grid-item`)?.offsetTop + 'px'
                        }
                     })"
                     wire:ignore
                >
                    <div x-ref="shadowBox" x-show="rowHover !== null" class="shadow-box "></div>

                    <div class="bold pr-1.5 pl-5">Student</div>
                    <div class="bold px-1.5">Nagekeken</div>
                    <div class="bold px-1.5">Score/Max</div>
                    <div class="bold px-1.5">Discr.</div>
                    <div class="bold pl-1.5 pr-5">Extra informatie</div>

                    <div class="col-span-5 h-[3px] bg-sysbase my-2 mx-5"></div>

                    @foreach($this->participantResults as $participant)
                        <div @class([
                                "grid-row contents group/row",
                                "hover:text-primary hover:shadow-lg" => !$participant->testNotTaken,
                                "disabled note" => $participant->testNotTaken,
                                ])
                             x-on:mouseover="rowHover = $el.dataset.row"
                             x-on:mouseout="rowHover = null"
                             data-row="{{ $loop->iteration }}"
                        >
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-1.5 pl-5 col-start-1 h-15 rounded-l-10">{{ $participant->name }} {{ $participant->test_take_status_id }}</div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                @if($participant->testNotTaken)
                                    <span>-/--</span>
                                @else
                                    <span>{{ $participant->rated }}</span>/
                                    <span>{{ $this->takenTestData['questionCount'] }}</span>
                                @endif
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                @if($participant->testNotTaken)
                                    <span>-/--</span>
                                @else
                                    <span>{{ $participant->score }}</span>/
                                    <span>{{ $this->takenTestData['maxScore'] }}</span>
                                @endif
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                @if($participant->testNotTaken)
                                    <span>--</span>
                                @else
                                    <span>{{ $participant->discrepancies }}</span>
                                @endif
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pl-1.5 pr-5 rounded-r-10 truncate">
                                <div class="flex items-center justify-between w-full ">
                                    <div class="flex items-center gap-2 truncate">
                                        <div class="flex items-center gap-2 text-sysbase">
                                            <x-tooltip class="w-[40px] h-[30px]" :icon-height="true" :icon-width="true">
                                                <x-slot:idleIcon>
                                                    <span class="flex items-center" x-show="!tooltip" x-cloak>
                                                        <x-icon.profile />
                                                        <x-icon.i-letter />
                                                    </span>
                                                </x-slot:idleIcon>
                                                <div class="grid grid-cols-[auto_auto] gap-2">
                                                    <div class="bold">@lang('test-take.Cijfer voor deze toets'):</div>
                                                    <div>{{ $participant->rating ?? '-' }}</div>

                                                    <div class="bold">@lang('test-take.Cijfer voor dit vak'):</div>
                                                    <div>{{ $participant->user->averageRatings->first()?->rating ?? '-'}}</div>

                                                    <div class="bold">@lang('test-take.Tijd totaal'):</div>
                                                    <div>{{ \Carbon\CarbonInterval::second($participant->total_time)->cascade()->forHumans() }}</div>

                                                    <div class="bold">@lang('test-take.Tijd per vraag'):</div>
                                                    <div>{{ \Carbon\CarbonInterval::second($participant->total_time / $participant->questions)->cascade()->forHumans() }}</div>

                                                    <div class="bold">@lang('test-take.Duurde het langst'):</div>
                                                    <div title="{{ $participant->longest_answer?->question?->title }}"
                                                         class="overflow-hidden text-ellipsis"
                                                    >
                                                        {{ $participant->longest_answer?->question?->title ?? __('general.unavailable') }}
                                                    </div>
                                                </div>
                                            </x-tooltip>
                                            @if($participant->testNotTaken)

                                            @else
                                                <x-icon.web />
                                                <x-icon.time-dispensation />
                                                <x-icon.speech-bubble />
                                                <x-icon.notepad />
                                            @endif

                                        </div>

                                        <span class="truncate ">{{ $participant->invigilator_note }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <x-button.icon
                                                    wire:click="$emit('openModal', 'message-create-modal', {receiver: '{{ $participant->user->uuid }}'})"
                                                    :title="__('message.Stuur bericht')"
                                            >
                                                <x-icon.envelope class="w-4 h-4" />
                                            </x-button.icon>

                                            <x-button.icon wire:click="assessParticipant('{{ $participant->uuid }}')"
                                                           :title="__('test-take.Nakijken')"
                                                           :disabled="$participant->testNotTaken"
                                                           :color="$participant->rated === $this->takenTestData['questionCount'] ? 'primary' : 'cta'"
                                            >
                                                <x-icon.review />
                                            </x-button.icon>
                                        </div>
                                        <div>
                                            <x-mark-badge rating="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endsection
@endif