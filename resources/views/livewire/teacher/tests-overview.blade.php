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