@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.completion-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms-completion"
            :disabled="isset($preview)"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
    />
@endsection
