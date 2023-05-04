@extends('livewire.modal.preview-attachment')

@section('content')
    <iframe class="w-full h-full"
            src="{{ $this->source }}"
    ></iframe>
@endsection
