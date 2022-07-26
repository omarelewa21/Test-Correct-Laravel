@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            lang="{{ $lang }}"
            allowWsc={{ $allowWsc }}
    />
@endsection
@section('question-cms-answer')
    @if($this->withDrawer)
        <div class="hidden flex w-full justify-end">
            <x-input.toggle-radio-row-with-title wire:model="question.subtype"
                                                 value-on="medium"
                                                 value-off="short"
            >
                <span class="bold">Maximale lengte van 250 karakters & geen tekstopmaak opties</span>
            </x-input.toggle-radio-row-with-title>
        </div>
    @endif
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.answer"
            editorId="{{ $answerEditorId }}"
            type="cms"
            lang="{{ $lang }}"
    />

@endsection