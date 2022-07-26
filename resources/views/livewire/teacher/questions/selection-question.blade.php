@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.selection-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            lang="{{ $lang }}"
            allowWsc={{ $allowWsc }}
    />
@endsection
