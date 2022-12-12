@extends('livewire.analyses.analyses-dashboard')

@section('analyses.page.title')
    <x-sticky-page-title class="top-20">
        <div class="flex items-center gap-4 ">
            @if($this->viewingAsTeacher())
                <x-button.back-round wire:click="redirectTeacherBack"/>
            @endif
            <div class="flex text-lg bold ">
                <span>{{  __('header.Analyses') }} </span>
            </div>
        </div>
    </x-sticky-page-title>
    @if($this->viewingAsTeacher())
        <h3> {{ $this->getHelper()->getForUser()->name_full }}  </h3>
    @endif
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
