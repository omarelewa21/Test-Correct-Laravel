@extends('layouts.test-take')

@section('cta')
    <div class="flex flex-col justify-center">
        <x-button.cta wire:click="surveillance">
            <span>@lang('header.Surveilleren')</span>
            <x-icon.arrow />
        </x-button.cta>
    </div>
@endsection

@section('action-buttons')
    <x-button.cta class="order-1"
                  wire:click="surveillance"
    >
        <span>@lang('header.Surveilleren')</span>
        <x-icon.arrow />
    </x-button.cta>
    <x-button.icon
            :disabled="true"
            class="order-3"
            title="{{ __('teacher.Toets instellingen') }}"
    >
        <x-icon.settings />
    </x-button.icon>
@endsection

@section('students')
    <div class="flex flex-col gap-4">
        <h2>@lang('test-take.Wachtkamer')</h2>
        <div class="flex flex-col py-5 px-7 bg-white rounded-10 content-section relative"
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
    </div>
@endsection