@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    <x-sticky-page-title class="top-20">
        <div class="flex items-center gap-4 ">
            <x-button.back-round wire:click="redirectBack"/>
            <div class="flex text-lg bold ">
                <span>{{  __('header.Analyses') }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {!! $subject->name !!} </span>
            </div>
        </div>

    </x-sticky-page-title>
@endsection

@section('analyses.page.title')
    <div class="flex pt-5 justify-between">
        <h1 class="flex pt-5"> {!! $subject->name !!} </h1>
        <x-button.primary class="hidden bg-purple-900 flex">Exporteren</x-button.primary>
    </div>
@endsection

@section('analyses.general-data')
    <x-content-section class="mb-8 w-full">
        <x-slot name="title">
            {{ __('student.Algemeen') }}
        </x-slot>

        <div class="flex flex-row">
            <x-partials.analyses-general-data :generalStats="$generalStats"/>
        </div>
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
            <div class="hidden">{{ $this->data }}</div>
            {{ __('student.p waarde leerdoelen') }}
        </x-slot>

        <div id="pValueChart" style="height: 400px;" class="relative">
            <x-empty-graph :show="$this->showEmptyStateForPValueGraph"></x-empty-graph>
        </div>
        <div x-data="analysesAttainmentsGraph( @entangle('dataValues') )"
             x-on:filters-updated.window="renderGraph"
        >
        </div>
    </x-content-section>
@endsection



{{--@section('analyses.top-items.title')--}}
{{--    {{ trans_choice('student.top leerdoelen om aan te werken', count($this->topItems)) }}--}}
{{--@endsection--}}
