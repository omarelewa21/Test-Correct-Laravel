@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    <x-sticky-page-title class="top-20">
        <div class="flex items-center gap-4 ">
            <x-button.back-round wire:click="redirectBack"/>
            <div class="flex text-lg bold">
                <span>{{ __('header.Analyses') }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {{ \tcCore\Subject::whereUuid($subject)->first()->name }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {{ __('student.leerdoel met nummer', ['number' => 4]) }}  <x-icon.chevron-small opacity="1"></x-icon.chevron-small> Sub Leerdoel 4</span>
            </div>
        </div>

    </x-sticky-page-title>
@endsection

@section('analyses.page.title')
    <h1 class="pt-10"> {{ __('student.subleerdoel met nummer', ['number' => 4]) }} </h1>
@endsection

@section('analyses.p-values-graph')
    <x-content-section>
        <x-slot name="title">
            <div class="hidden">{{ $this->data }}</div>
            {{ __('student.p waarde subsubleerdoelen') }}
        </x-slot>

        <div id="pValueChart" style="height: 400px;"></div>
        <div x-data="analysesAttainmentsGraph( @entangle('dataValues') )"
             x-on:filters-updated.window="renderGraph"
        >
        </div>
    </x-content-section>
@endsection

@section('analyses.page.title')
    <h2 class="pt-4">Subleerdoel 4</h2>
@endsection

@section('analyses.attainment.description')
    <div class="border-t border-secondary pt-10 mb-10">
        <h4>{{ __('student.description') }}</h4>
        <div>{{ $this->attainment->description }}</div>
    </div>
@endsection





