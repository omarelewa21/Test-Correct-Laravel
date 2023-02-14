@extends('livewire.modal.preview-attachment')

@section('content')
    <iframe class="w-full h-full"
            src="{{ route('teacher.preview.question-pdf-attachment-show', ['attachment' => $this->attachment->uuid, 'question' => $this->questionUuid]) }}"
    ></iframe>
@endsection
