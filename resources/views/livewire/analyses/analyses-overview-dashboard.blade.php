@extends('livewire.analyses.analyses-dashboard')

@section('analyses.header.title')
        <div class="flex items-center gap-4 ">
            @if($this->viewingAsTeacher())
                <x-button.back-round wire:click="redirectTeacherBack"/>
            @endif
            <div class="flex text-lg bold ">
                <span>{{  __('header.Analyses') }} </span>
            </div>
        </div>
@endsection

@section('analyses.page.title')
    <div class="flex pt-5 justify-between">
        <div class="flex flex-col pt-5">
            @if($this->viewingAsTeacher())
                <h2>{{ $this->getHelper()->getForUser()->name_full }}</h2>
            @endif
        </div>
        <x-button.primary class="hidden bg-purple-900 flex">Exporteren</x-button.primary>
    </div>
@endsection


@section('analyses.p-values-graph')

    <x-content-section>
        <x-slot name="title">
            {{ __('student.Percentage per vak') }}
        </x-slot>


        <div x-data="analysesSubjectsGraph('pValueChart')"
             x-on:filters-updated.window="updateGraph();"
        >            <div id="pValueChart" style="height: 400px;" class="relative" wire:ignore>
                <x-empty-graph x-show="showEmptyState" :show="true"></x-empty-graph>
            </div>
        </div>
    </x-content-section>

@endsection

@section('analyses.top-items.title')
        {{ trans_choice('student.top vakken om aan te werken', count($this->topItems)) }}
@endsection

@section('analyses.p-values-time-series-graph')
    <BR>
    <x-content-section>
        <x-slot name="title">
            {{ __('student.ontwikkeling percentage over tijd') }}
        </x-slot>


        <div x-data="analysesSubjectsTimeSeriesGraph('pValueTimeSeriesChart')"
             x-on:filters-updated.window="updateGraph();"
        >            <div id="pValueTimeSeriesChart" style="height: 400px;" class="relative" wire:ignore>
                <x-empty-graph x-show="showEmptyState" :show="true"></x-empty-graph>
            </div>
        </div>
    </x-content-section>


@endsection


