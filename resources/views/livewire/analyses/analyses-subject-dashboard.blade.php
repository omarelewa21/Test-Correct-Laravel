@extends('livewire.analyses.analyses-dashboard')

@section('analyses.header.title')
    <div class="flex items-center gap-4 ">
        <x-button.back-round wire:click="redirectBack"/>
        <div class="flex text-lg bold ">
            <span>{{  __('header.Analyses') }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {!! $subject->name !!} </span>
        </div>
    </div>
@endsection

@section('analyses.page.title')
    <div class="flex pt-5 justify-between">
        <div class="flex flex-col pt-5">
            <h1>{!! $subject->name !!}</h1>
            @if($this->viewingAsTeacher())
                <h2>{{ $this->getHelper()->getForUser()->name_full }}</h2>
            @endif
        </div>
        <x-button.primary class="hidden bg-purple-900 flex">Exporteren</x-button.primary>
    </div>
@endsection

@section('analyses.general-data')
    <x-content-section class="mb-8 w-full">
        <x-slot name="title">
            {{ __('student.Algemeen') }}
        </x-slot>
        @if ($this->showEmptyStateForGeneralStats())
            <div class="min-h-[300px] relative">
                <x-empty-graph show="true"></x-empty-graph>
            </div>
        @else
            <div class="flex flex-row">
                <x-partials.analyses-general-data :generalStats="$generalStats"/>
            </div>
        @endif
    </x-content-section>

    <div class="divider my-6"></div>
@endsection

@section('analyses.p-values-graph')
    <div class="flex justify-between mb-5">
        <h2 class="flex">{{ __('student.overzicht p-waardes') }}</h2>
        <div class="flex">
            <x-button.slider
                    class="flex gap-2 items-center"
                    label="{{  __('Weergave per') }}"
                    :options="$this->attainmentModeOptions"
                    wire:model="attainmentMode"
            ></x-button.slider>
        </div>
    </div>

    <x-content-section>
        <x-slot name="title">
            {{--            <div class="hidden">{{ $this->data }}</div>--}}
            @if($this->attainmentMode === \tcCore\Attainment::TYPE)
                {{ __('student.p waarde eindtermen') }}
            @else
            {{ __('student.p waarde leerdoelen') }}
            @endif
        </x-slot>
        <div x-data="analysesAttainmentsGraph('pValueChart')"
             x-on:filters-updated.window="updateGraph"
        >
            <div id="pValueChart" style="height: 400px;" class="relative" wire:ignore>
                <x-empty-graph x-show="showEmptyState" :show="true"></x-empty-graph>
            </div>

        </div>
    </x-content-section>
@endsection



@section('analyses.top-items.title')
    @if($this->attainmentMode === \tcCore\Attainment::TYPE)
        {{ trans_choice('student.top eindtermen om aan te werken', count($this->topItems)) }}
    @else
    {{ trans_choice('student.top leerdoelen om aan te werken', count($this->topItems)) }}
    @endif
@endsection

@section('analyses.p-values-time-series-graph')
    <BR>
    <x-content-section>
        <x-slot name="title">
                {{ __('student.ontwikkeling p-waarde over tijd') }}
        </x-slot>


        <div x-data="analysesSubjectsTimeSeriesGraph('pValueTimeSeriesChart')"
             x-on:filters-updated.window="updateGraph();"
        >            <div id="pValueTimeSeriesChart" style="height: 400px;" class="relative" wire:ignore>
                <x-empty-graph x-show="showEmptyState" :show="true"></x-empty-graph>
            </div>
        </div>
    </x-content-section>


@endsection
