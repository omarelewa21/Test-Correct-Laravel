@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
    />
@endsection

@section('question-cms-answer')
    <div x-data="{ show: false }">
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

    <div class="flex flex-1 min-h-[500px] w-full border border-allred rounded-10 mt-4 items-center justify-center relative overflow-auto">

        @if($this->isOldDrawingQuestion())

                <div class="absolute top-0 left-0 w-full h-full">
                    <img class="object-cover" src="{{ $this->question['answer'] }}" alt="">
                </div>

                <div class="max-w-2xl z-0  p-8 flex flex-col items-center justify-center relative rounded-10 overflow-auto">
                    <div class="absolute bg-white opacity-80 w-full h-full"></div>
                    <div class="z-0 flex flex-col items-center justify-center">
                        <x-button.primary @click="show = !show" >
                            Antwoordmodel tekenen
                        </x-button.primary>
                        <p class="text-note text-sm text-center mt-4">{{ __('cms.waarschuwing_aanpassen_oude_tekenvraag') }} </p>
                    </div>
                </div>

        @else
            <div class="absolute top-0 left-0 w-full h-full">
                <svg style="width:800px; height:800px" id="" xmlns="http://www.w3.org/2000/svg" style="--cursor-type-locked:var(--cursor-crosshair); --cursor-type-draggable:var(--cursor-crosshair);">
                    {!!  base64_decode($this->question['answer_svg']) !!}
                    {!!  base64_decode($this->question['question_svg']) !!}
                </svg>
            </div>
            <div class="max-w-2xl z-0  p-8 flex flex-col items-center justify-center relative rounded-10 overflow-auto">
                <div class="absolute bg-white opacity-80 w-full h-full"></div>
                <div class="z-0 flex flex-col items-center justify-center">
                    <x-button.primary @click="show = !show" >
                        Antwoordmodel tekenen
                    </x-button.primary>
                </div>
            </div>
        @endif

        </div>
        <x-modal.question-editor-drawing-modal/>
    </div>
@endsection
