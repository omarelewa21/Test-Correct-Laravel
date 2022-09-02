@extends('livewire.student.analyses.analyses-dashboard')

@section('analyses.header.title')
    {{ __('header.Analyses') }}
@endsection

@section('analyses.p-values-per-item.title')
    {{ __('student.p waarde vakken') }}
@endsection

@section('analyses.top-items.title')
    {{ trans_choice('student.top vakken om aan te werken', count($this->topItems)) }}
@endsection