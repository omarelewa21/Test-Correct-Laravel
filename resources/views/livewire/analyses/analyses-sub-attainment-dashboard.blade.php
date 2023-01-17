@extends('livewire.analyses.analyses-dashboard')

@section('analyses.header.title')
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
@endsection

@section('analyses.page.title')
    <div class="flex flex-col">
        <h1 class="flex pt-5">{{ $attainment->getSubNameWithNumber($attainmentOrderNumber)  }}</h1>
        @if($this->viewingAsTeacher())
            <h3> {{ $this->getHelper()->getForUser()->name_full }}  </h3>
        @endif
    </div>
@endsection

@section('analyses.p-values-graph')
    <div class="flex justify-between mb-5">
        <h2 class="flex">{{ __('student.overzicht Percentages') }}</h2>
    </div>
    <x-content-section>
        <x-slot name="title">
            <div class="hidden">{{ $this->data }}</div>
            @if ($attainment->is_learning_goal == 1)
            {{ __('student.Percentage per subsubleerdoel') }}
                @else
                {{ __('student.Percentage per subsubeindterm') }}
            @endif
        </x-slot>


        <div x-data="analysesAttainmentsGraph('pValueChart')"
             x-on:filters-updated.window="updateGraph"
        ><div id="pValueChart" style="height: 400px;" class="relative" wire:ignore>
                <x-empty-graph x-show="showEmptyState" :show="true"></x-empty-graph>
            </div>
        </div>
    </x-content-section>
@endsection

@section('analyses.page.title')
    <h2 class="pt-4">Subleerdoel 4</h2>
    @if($this->viewingAsTeacher())
        <h3> {{ $this->getHelper()->getForUser()->name_full }}  </h3>
    @endif
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

        @if ($this->showEmptyStateForGeneralStats())
            <div class="min-h-[300px] relative">
                <x-empty-graph show="true"></x-empty-graph>
            </div>
        @else
            <div class="flex flex-row">
                <x-partials.analyses-general-data :generalStats="$generalStats"/>
            </div>
        @endif
    </x-content-section>

    <div class="divider my-6"></div>
@endsection
@section('analyses.top-items.title')
    {{ trans_choice('student.top sub subleerdoelen om aan te werken', count($this->topItems)) }}
@endsection
