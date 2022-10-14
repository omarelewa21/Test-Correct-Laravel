@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    <x-sticky-page-title class="top-20">
        <div class="flex items-center gap-4 ">
            <x-button.back-round wire:click="redirectBack"/>
            <div class="flex text-lg bold ">
                <span>{{  __('header.Analyses') }} <x-icon.chevron-small opacity="1"></x-icon.chevron-small> {{$subject->name}} </span>
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

            <div class="md:w-1/3 mr-5 gap-4 flex flex-col">
                <div class="flex flex-col">
                    <span>{{ __('student.aantal toetsen gemaakt')}}</span>
                    <span class="bold">{{ $generalStats['tests_taken'] }}</span>
                </div>
                <div class="flex flex-col">
                    <span>{{ __('student.gemiddelde p-waarde') }}</span>
                    <span>{{ __('student.o.b.v. aantal vragen', ['count'=> $generalStats['tests_questions']]) }}<span class="bold"> P {{ number_format($generalStats['tests_pvalue_average'], 2) }} </span></span>
                </div>
                <div class="flex flex-col">
                    <div>{{ __('student.gemiddeld cijfer') }}</div>
                    <div>
                        <x-mark-badge :rating="$generalStats['tests_rating_average']"></x-mark-badge>
                        <x-button.text-button type="link" size="sm">
                            <span class="bold">{{ __('student.Bekijk cijferlijst') }}</span>
                            <x-icon.arrow />
                        </x-button.text-button>
                    </div>
                </div>
            </div>
            <div class="md:w-1/3 mr-5 gap-4 flex flex-col">
                <div class="flex flex-col">
                    <span>{{ __('student.aantal toetsen gemaakt')}}</span>
                    <span class="bold">{{ $generalStats['assignments_taken'] }}</span>
                </div>
                <div class="flex flex-col">
                    <span>{{ __('student.gemiddelde p-waarde') }}</span>
                    <span>{{ __('student.o.b.v. aantal vragen', ['count'=> $generalStats['assignments_questions']]) }}<span class="bold"> P {{ number_format($generalStats['assignments_pvalue_average'], 2) }} </span></span>
                </div>
                <div class="flex flex-col">
                    <div>{{ __('student.gemiddeld cijfer') }}</div>
                    <div>
                        <x-mark-badge :rating="$generalStats['assignments_rating_average']"></x-mark-badge>
                        <x-button.text-button type="link" size="sm">
                            <span class="bold">{{ __('student.Bekijk cijferlijst') }}</span>
                            <x-icon.arrow />
                        </x-button.text-button>
                    </div>
                </div>
            </div>

            <div class="md:w-1/3 mr-5">
                Kolom 3
            </div>
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

        <div id="pValueChart" style="height: 400px;"></div>
        <div x-data="analysesAttainmentsGraph( @entangle('dataValues') )"
             x-on:filters-updated.window="renderGraph"
        >
        </div>
    </x-content-section>
@endsection



{{--@section('analyses.top-items.title')--}}
{{--    {{ trans_choice('student.top leerdoelen om aan te werken', count($this->topItems)) }}--}}
{{--@endsection--}}
