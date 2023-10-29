@extends('livewire.teacher.test-plan-modal')

@section('retake-original-date')
    <x-input.group :label="__('test-take.Afname was op')">
        <x-input.text :disabled="true" class="!text-sysbase" :value="$this->testTake->time_start->format('d-m-Y')" />
    </x-input.group>
@endsection

@section('retake-period')
    <x-input.text :disabled="true" :value="$this->testTake->period?->name"/>
@endsection

@section('retake-weight')
    <x-input.text :disabled="true" :value="$this->testTake->weight" class="max-w-[100px]"/>
@endsection