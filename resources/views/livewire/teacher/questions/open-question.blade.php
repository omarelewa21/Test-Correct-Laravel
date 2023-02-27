@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            :disabled="isset($preview)"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
    />
@endsection
@section('question-cms-answer')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.answer"
            editorId="{{ $answerEditorId }}"
            type="cms"
            :disabled="isset($preview)"
            lang="{{ $lang }}"
            selid="answer-textarea"
    />
@endsection