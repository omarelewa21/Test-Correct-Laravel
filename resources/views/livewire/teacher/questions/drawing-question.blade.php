@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
    />
@endsection

@section('question-cms-answer')
    <div x-data="{show: false}">
        <div class="flex mb-4 space-x-4">
            <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                           :toolTip="__('cms.all_or_nothing_tooltip_text')"
            >
                <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
            </x-input.toggle-row-with-title>
            <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                           :toolTip="__('cms.all_or_nothing_tooltip_text')"
            >
                <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
            </x-input.toggle-row-with-title>
            <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                           :toolTip="__('cms.all_or_nothing_tooltip_text')"
            >
                <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
            </x-input.toggle-row-with-title>
        </div>

        <div>
            <span>{{ __('cms.Teken in de tekentool het antwoordmodel voor de vraag.') }}</span>
        </div>

        <div class="flex flex-1 min-h-[500px] w-full border border-allred rounded-10 mt-4 items-center justify-center">
            <div class="flex">
                <x-button.primary @click="show = !show; drawingApp.init()">
                    Antwoordmodel tekenen
                </x-button.primary>
            </div>

        </div>
        <x-modal.question-editor-drawing-modal/>
    </div>
@endsection