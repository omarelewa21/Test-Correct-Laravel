@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.page.title')
    <h1 class="pt-10"> {{ __('header.Analyses') }} </h1>
@endsection




@section('analyses.p-values-graph')
    @if(auth()->user()->getKey() !== $this->getUser()->getKey())
        <h1> {{ $this->getUser()->name_full }}  </h1>
    @endif
    <x-content-section>
        <x-slot name="title">
            <div class="hidden">{{ $this->data }}</div>
            {{ __('student.p waarde vakken') }}
        </x-slot>

        <div id="pValueChart" style="height: 400px;"></div>
        <div x-data="analysesSubjectsGraph( @entangle('dataValues') )"
             x-on:filters-updated.window="renderGraph"
        >
        </div>
    </x-content-section>

@endsection

@section('analyses.top-items.title')
    {{--    {{ trans_choice('student.top vakken om aan te werken', count($this->topItems)) }}--}}
@endsection
