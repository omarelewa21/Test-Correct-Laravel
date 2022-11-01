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
                    <a href="{{ route('student.analyses.subattainment.show', ['baseAttainment' => $parentParentAttainment->uuid, 'subject' => $subject]) }}">
                      {{ $parentParentAttainment->name }}
                    </a>
                    <x-icon.chevron-small opacity="1"></x-icon.chevron-small>
                     <a href="{{ route('student.analyses.subattainment.show', ['baseAttainment' => $parentAttainment->uuid, 'subject' => $subject]) }}">
                    {{ $parentAttainment->getSubNameWithNumber($parentAttainment->getOrderNumber()) }}
                     </a>
                     <x-icon.chevron-small opacity="1"></x-icon.chevron-small>
                    {{ $attainment->getSubSubNameWithNumber($attainment->getOrderNumber()) }}
                </span>
            </div>
        </div>

    </x-sticky-page-title>
@endsection

@section('analyses.page.title')
    <h2 class="pt-4">Subsubleerdoel 4</h2>
@endsection

@section('analyses.attainment.description')
    <div class="py-4 text-lg">
        <span class="bold">{{ __('student.description') }}</span>
        <div>{{ $this->attainment->description }}</div>
    </div>
@endsection
@section('analyses.general-data')
    <x-content-section class="mb-8 w-full">
        <x-slot name="title">
            {{ __('student.Algemeen') }}
        </x-slot>

        <div class="flex flex-row">
            <x-partials.analyses-general-data :generalStats="$generalStats"/>
        </div>
    </x-content-section>

    <div class="divider my-6"></div>
@endsection




