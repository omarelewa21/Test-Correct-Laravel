@extends('layouts.test-take')

@section('cta')
    <div class="flex flex-col justify-center">
        @if($this->canStartTestTake())
            <x-button.cta wire:click="startTake">
                <span>@lang('test-take.Afnemen')</span>
                <x-icon.arrow />
            </x-button.cta>
        @else
            @if(!$this->testTake->time_start->isToday())
                <span class="bold text-lg">@lang('test-take.toetsafname is niet vandaag gepland')</span>
            @elseif(!$this->hasStudentStartRequirement())
                <span class="bold text-lg">@lang('test-take.Geen studenten beschikbaar')</span>
            @else
                <span class="bold text-lg">@lang('test-take.Afname is niet mogelijk')</span>
            @endif
        @endif
    </div>
@endsection

@section('action-buttons')
    <x-button.cta class="order-1"
                  :disabled="!$this->canStartTestTake()"
                  wire:click="startTake"
    >
        <span>@lang('test-take.Afnemen')</span>
        <x-icon.arrow />
    </x-button.cta>
    <x-button.icon
            wire:click="$emit('openModal','teacher.test-take-edit-modal', {testTake: '{{ $this->testTake->uuid }}' })"
            class="order-3"
            title="{{ __('teacher.Toets instellingen') }}"
    >
        <x-icon.settings />
    </x-button.icon>
@endsection

@section('waitingRoom')
    <div class="flex flex-col gap-4">
        <h2>@lang('test-take.Wachtkamer')</h2>
        <div class="flex flex-col pt-4 pb-10 px-10 bg-white rounded-10 content-section relative"
             x-data="{plannedTab: 'students'}"
             x-cloak
        >
            <x-menu.tab.container :withTileEvents="false" max-width-class="">
                <x-menu.tab.item tab="students" menu="plannedTab" selid="test-take-overview-tab-taken" class="-ml-2">
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
                            'student-absent' => !$participant->present,
                            'student-present' => $participant->present
                            ])
                                 wire:key="participant-{{ $participant->uuid }}-@js($participant->present)"
                            >
                                <span>{{ $participant->name }}</span>
                                <x-icon.close-small class="!text-sysbase"
                                                    wire:click="removeParticipant('{{ $participant->uuid }}')"
                                                    x-on:click="$el.parentElement.style.opacity = '75%'"
                                />
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

                @if($this->initialized)
                    <x-button.text wire:click="$emit('openModal','teacher.test-take-edit-modal', {testTake: '{{ $this->testTake->uuid }}' })"
                                   class="mt-4"
                    >
                        <x-icon.plus />
                        <span>@lang('test-take.Studenten toevoegen')</span>
                    </x-button.text>
                @endif
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
                            <x-icon.close-small class="!cursor-pointer"
                                                x-on:click="$el.parentElement.style.opacity = '75%'"
                                                wire:click="removeInvigilator('{{ $invigilatorUser->invigilator_uuid }}')"
                            />
                        </div>
                    @empty
                        <span>@lang('test-take.Geen surveillanten beschikbaar')</span>
                    @endforelse
                </div>

                <x-button.text wire:click="$emit('openModal','teacher.test-take-edit-modal', {testTake: '{{ $this->testTake->uuid }}' })"
                               class="mt-4"
                >
                    <x-icon.plus />
                    <span>@lang('test-take.Surveillanten toevoegen')</span>
                </x-button.text>

            </div>
        </div>
    </div>
@endsection