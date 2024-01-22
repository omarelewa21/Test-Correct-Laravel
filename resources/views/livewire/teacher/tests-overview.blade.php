@extends('livewire.teacher.tests-overview-layout')

@section('container')
    <div id="testbank"
        selid="tests-overview-page"
        wire:init="handleReferrerActions()"
        class="flex flex-col w-full min-h-full bg-lightGrey border-t border-secondary top-0"
    >
        <div x-data="{openTab: @entangle('openTab')}">

@endsection

@section('create-test-button')
    @if(!auth()->user()->isValidExamCoordinator())
        <div class="flex space-x-2.5">
            <x-button.cta class="px-4"
                          wire:click="$emit('openModal', 'teacher.test-start-create-modal')"
                          selid="create-new-test-button"
            >
                <x-icon.plus/>
                <span>{{ __('general.create test') }}</span>
            </x-button.cta>
        </div>
    @endif
@endsection

@section('clear-filters-button')
    <x-button.text class="ml-auto text-base"
                          size="sm"
                          wire:click="clearFilters(true)"
                          x-on:click="$dispatch('enable-loading-grid');document.getElementById('testbank-{{ $this->openTab }}-active-filters').innerHTML = '';"
                          :disabled="!$this->hasActiveFilters()"
    >
        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
        <x-icon.close-small/>
    </x-button.text>
@endsection