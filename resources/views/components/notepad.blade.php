@if($showNotepad)
    <div class="absolute top-28 left-20 z-30 bg-off-white p-5 rounded border border-blue-grey">
        <button wire:click="closeNotepad">close X</button>
        <textarea wire:model="notepadText">Textarea</textarea>
    </div>
@endif
