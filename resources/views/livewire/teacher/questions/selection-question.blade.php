@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.selection-textarea
            wire:model.debounce.300ms="question.question"
            editorId="{{ $questionEditorId }}"
    />
@endsection
