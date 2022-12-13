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
            <div class="hidden">{{ $this->data }}</div>
            {{ __('student.p waarde vakken') }}
        </x-slot>

        <div id="pValueChart" style="height: 400px;" wire:ignore></div>
        <div x-data="analysesSubjectsGraph( @entangle('dataValues') )"

             x-on:filters-updated.window="renderGraph"
        >
        </div>
    </x-content-section>

@endsection

@section('analyses.top-items.title')
        {{ trans_choice('student.top vakken om aan te werken', count($this->topItems)) }}
@endsection
