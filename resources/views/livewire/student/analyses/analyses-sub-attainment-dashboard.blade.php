@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    <x-sticky-page-title class="top-20">
        <div class="flex items-center gap-4 ">
            <x-button.back-round wire:click="redirectBack"/>
            <div class="flex text-lg bold">
                <span>
                    <a href="{{ route('student.analyses.show') }}">{{ __('header.Analyses') }}</a>
                    <x-icon.chevron-small opacity="1"></x-icon.chevron-small>
                    <a href="{{ route('student.analyses.subject.show', $subject) }}">
                        {!!  \tcCore\Subject::whereUuid($subject)->first()->name !!}
                    </a>
                    <x-icon.chevron-small opacity="1"></x-icon.chevron-small>
                    <a wire:click="redirectBack" class="cursor-pointer">
                    {{ \tcCore\BaseAttainment::find($attainment->attainment_id)->name }}
                    </a>
                    <x-icon.chevron-small opacity="1">
                    </x-icon.chevron-small>{{ $attainment->getSubNameWithNumber($attainmentOrderNumber) }}
                </span>
            </div>
        </div>

    </x-sticky-page-title>
@endsection

@section('analyses.page.title')
    <h1 class="pt-10"> {{ $attainment->getSubNameWithNumber($attainmentOrderNumber)  }} </h1>
@endsection

@section('analyses.p-values-graph')
    <x-content-section>
        <x-slot name="title">
            <div class="hidden">{{ $this->data }}</div>
            @if ($attainment->is_learning_goal == 1)
            {{ __('student.p waarde subsubleerdoelen') }}
                @else
                {{ __('student.p waarde subsubeindtermen') }}
            @endif
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

@section('analyses.page.title')
    <h2 class="pt-4">Subleerdoel 4</h2>
@endsection

@section('analyses.attainment.description')
    <div class="border-t border-secondary pt-10 mb-10">
        <h4>{{ __('student.description') }}</h4>
        <div>{{ $this->attainment->description }}</div>
    </div>
@endsection





