@if($question->note_type == 'TEXT')
    <x-button.secondary size="sm" class="mb-4" wire:click="openNotepad">
        <span>{{__('test_take.open_notepad')}}</span>
    </x-button.secondary>
@endif