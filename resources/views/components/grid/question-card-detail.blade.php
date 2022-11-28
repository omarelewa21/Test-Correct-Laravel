@extends('components.grid.question-card-base')

@section('question-closed')
    @if($testQuestion->closeable)
        <x-icon.locked class="mt-auto mb-2"/>
    @else
        <x-icon.unlocked class="mt-auto mb-2"/>
    @endif
@endsection