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

@section('analyses.p-values-graph')
    <x-content-section class="mb-8 w-full">
        <x-slot name="title">
            {{ __('student.Algemeen') }}
        </x-slot>
        <div class="flex flex-row">

            @foreach(['tests', 'assignments'] as $kind)
                <div class="flex-1 gap-4 flex flex-col" x-data="{pValue: {{ number_format($generalStats[$kind.'_pvalue_average'], 2)  }} }">
                    <div class="flex flex-col">
                        <span>{{ __('student.aantal '. $kind. ' gemaakt')}}</span>
                        <span class="bold">{{ $generalStats[$kind.'_taken'] }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span>{{ __('student.gemiddelde p-waarde') }}</span>
                        <div class="inline-block">
                            <span>
                                {{ __('student.o.b.v. aantal vragen', ['count'=> $generalStats[$kind.'_questions']]) }}
                                <span class="bold px-0.5">P {{ number_format($generalStats[$kind.'_pvalue_average'], 2) }}</span>
                            </span>
                            <div class="inline-flex relative">
                                <span x-show="pValue" class="pvalue-indicator"
                                      style="--pvalue-indicator-ball-left: -2px"
                                      :style="{'left': `${pValue * 100}%`}"
                                ></span>
                                <div class="inline-flex rounded-md overflow-hidden w-[70px] h-2.5">
                                    <span class="flex-1 inline-flex bg-allred"></span>
                                    <span class="flex-1 inline-flex bg-orange"></span>
                                    <span class="flex-1 inline-flex bg-student"></span>
                                    <span class="flex-1 inline-flex bg-lightgreen"></span>
                                    <span class="flex-1 inline-flex bg-cta"></span>
                                    <span class="flex-1 inline-flex bg-ctamiddark"></span>
                                    <span class="flex-1 inline-flex bg-ctadark"></span>
                                </div>
                            </div>
                            <span class="note text-xs">1.00</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div>{{ __('student.gemiddeld cijfer') }}</div>
                        <div class="flex gap-4 items-center">
                            <x-mark-badge :rating="$generalStats[$kind.'_rating_average']"></x-mark-badge>

                            <x-button.text-button wire:click="showGrades">
                                <span class="bold">{{ __('student.Bekijk cijferlijst') }}</span>
                                <x-icon.arrow />
                            </x-button.text-button>
                        </div>
                    </div>
                </div>
            @endforeach
{{--            <div class="md:w-1/3 mr-5"></div>--}}
        </div>


    </x-content-section>

    <div class="flex justify-between mb-5">
        <h1 class="flex">Overzicht P-waardes</h1>
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

        <div id="pValueChart" style="height: 400px;">
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
