@if($showNotepad)
    <div class="absolute -top-10 flex flex-col px-4 pb-4 z-30 bg-white rounded-10 shadow-lg border border-blue-grey w-3/5 h-auto">
        <div class="flex justify-end">
            <x-button.text wire:click="closeNotepad"><x-icon.close/></x-button.text>
        </div>
        <div class="flex-1 h-80">
            <x-input.group label="Notitieblok" class="w-full">
                <x-input.textarea  spellcheck="false" wire:model="notepadText" class="h-full"></x-input.textarea>
            </x-input.group>
        </div>
    </div>
@endif
