@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.selection-textarea
            wire:model.defer="question.question"
            editorId="{{ $questionEditorId }}"
    />
@endsection
