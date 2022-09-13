@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    <x-sticky-page-title class="top-20">
        <div class="flex items-center gap-4 ">
            <x-button.back-round wire:click="redirectBack"/>
            <div class="flex text-lg bold">
                <span>{{ __('header.Analyses') }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {{ \tcCore\Subject::whereUuid($subject)->first()->name }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {{ __('student.leerdoel met nummer', ['number' => 4]) }}</span>
            </div>
        </div>
        <x-button.primary class="bg-purple-900">Exporteren</x-button.primary>
    </x-sticky-page-title>
@endsection

@section('analyses.p-values-graph')
    <x-content-section>
        <x-slot name="title">
            <div class="hidden">{{ $this->data }}</div>
            {{ __('student.p waarde subleerdoelen') }}
        </x-slot>

        <div id="pValueChart" style="width: 900px; height: 400px;"></div>
        <div x-data="analysesAttainmentsGraph( @entangle('dataValues') )"
             x-on:filters-updated.window="renderGraph"
        >
        </div>
    </x-content-section>

@endsection


@section('analyses.top-items.title')
    {{ trans_choice('student.top subleerdoelen om aan te werken', count($this->topItems)) }}
@endsection
