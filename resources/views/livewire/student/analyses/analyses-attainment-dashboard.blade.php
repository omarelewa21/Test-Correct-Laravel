@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    <x-sticky-page-title class="top-20">
        <x-button.back-round wire:click="redirectBack"/>
        <div class="flex text-lg bold">
            <span>{{$attainment->description}}</span>
        </div>
    </x-sticky-page-title>
@endsection



@section('analyses.p-values-per-item.title')
    {{ __('student.p waarde subleerdoelen') }}
@endsection

@section('analyses.p-values-graph')
    <div x-data="analysesAttainmentsGraph( @entangle('dataValues') )"
         x-on:filters-updated.window="renderGraph"
    >
    </div>
@endsection

@section('analyses.top-items.title')
    {{ trans_choice('student.top subleerdoelen om aan te werken', count($this->topItems)) }}
@endsection
