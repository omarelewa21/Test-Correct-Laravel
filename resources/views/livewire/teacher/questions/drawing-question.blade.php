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
        <div id="delete-modal"
             class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-[101]"

             x-show="show"
             x-cloak
             @delete-modal.window="[item, identifier] = $event.detail;show = true;"
             x-transition:enter="ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
        >
            <div x-show="show" class="fixed inset-0 transform " x-on:click="show = false"
                 x-transition:enter="ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div x-show="show"
                 class="relative top-1/2 flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform -translate-y-1/2  max-w-max sm:mx-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="px-2.5 flex justify-between items-center mt-2">
                    <h2>{{ __('cms.delete') }}</h2>
                    <x-icon.close class="cursor-pointer hover:text-primary" @click="show = false"/>
                </div>
                <div class="divider mb-5 mt-2.5"></div>
                <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 ">
                    <x-question.drawing.drawing-tool />

                </div>
            </div>

        </div>
    </div>
@endsection