@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.300ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms-completion"
    />
@endsection
