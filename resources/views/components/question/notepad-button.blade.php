@props([
'question',
])
@if($question->note_type == 'TEXT')
    <x-button.secondary size="sm" class="mb-4" wire:click="openNotepad" wire:key="note-{{ $this->number }}" >
        <span>{{__('test_take.open_notepad')}}</span>
    </x-button.secondary>
@endif