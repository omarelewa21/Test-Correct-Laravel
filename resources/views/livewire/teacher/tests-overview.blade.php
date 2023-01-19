@extends('livewire.teacher.tests-overview-layout')

@section('container')
    <div id="testbank"
         x-data="{
            openTab: $wire.entangle('openTab')
         }"
         wire:init="handleReferrerActions()"
         class="flex flex-col w-full min-h-full bg-lightGrey border-t border-secondary top-0"
    >
@endsection

@section('create-test-button')
    @if(!auth()->user()->isValidExamCoordinator())
        <div class="flex space-x-2.5">
            <x-button.cta class="px-4" wire:click="$emit('openModal', 'teacher.test-start-create-modal')">
                <x-icon.plus-2/>
                <span>{{ __('general.create test') }}</span>
            </x-button.cta>
        </div>
    @endif
@endsection

@section('clear-filters-button')
    <x-button.text-button class="ml-auto text-base"
                          size="sm"
                          wire:click="clearFilters()"
                          x-on:click="$dispatch('enable-loading-grid');document.getElementById('testbank-{{ $this->openTab }}-active-filters').innerHTML = '';"
                          :disabled="!$this->hasActiveFilters()"
    >
        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
        <x-icon.close-small/>
    </x-button.text-button>
@endsection