@extends('layouts.test-take')

@section('kaas')
    <div class="flex flex-col py-5 px-7 bg-white rounded-10 content-section"
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

        <div x-show="plannedTab === 'students'"
             class="flex flex-col w-full pt-5"
        >
            <div class="flex w-full relative flex-wrap gap-2">
                @if($this->initialized)
                    @forelse($this->participants as $participant)
                        <div @class([
                            'filter-pill px-4 gap-2 h-10',
                            'disabled' => !$participant->present,
                            'enabled' => $participant->present
                            ])
                             @unless($participant->present)
                                 wire:click="removeParticipant(@js($participant->uuid))"
                             @endif
                             wire:key="participant-{{ $participant->uuid }}-@js($participant->present)"
                        >
                            <span>{{ $participant->name }}</span>
                            <x-icon.close-small />
                        </div>
                    @empty
                        <span>@lang('general.unavailable')</span>
                    @endforelse
                @else
                    <div class="flex w-full h-full items-center justify-center">
                        <x-icon.loading-large class="animate-spin" />
                    </div>
                @endif
            </div>

            @if($this->initialized)
                <x-button.text-button
                        wire:click="$emit('openModal','teacher.test-take-edit-modal', {testTake: '{{ $this->testTake->uuid }}' })">
                    <x-icon.plus />
                    <span>@lang('test-take.Studenten toevoegen')</span>
                </x-button.text-button>
            @endif
        </div>
        <div x-show="plannedTab === 'invigilators'"
             class="flex flex-col w-full pt-5"
        >
            <div class="flex w-full relative flex-wrap gap-2">
                @forelse($this->invigilators as $invigilator)
                    <div class="filter-pill px-4 gap-2 h-10 enabled"
                         wire:click="removeInvigilator(@js($invigilator->uuid))"
                         wire:key="invigilator-{{ $invigilator->uuid }}"
                    >
                        <span wire:ignore.self>{{ $invigilator->getFullNameWithAbbreviatedFirstName() }}</span>
                        <x-icon.close-small />
                    </div>
                @empty
                    <span>@lang('general.unavailable')</span>
                @endforelse
            </div>

            <x-button.text-button
                    wire:click="$emit('openModal','teacher.test-take-edit-modal', {testTake: '{{ $this->testTake->uuid }}' })">
                <x-icon.plus />
                <span>@lang('test-take.Surveillanten toevoegen')</span>
            </x-button.text-button>

        </div>
    </div>
@endsection