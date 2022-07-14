<div x-data="{

}">
    @if ($this->test->canDelete(auth()->user()))
        <x-button.primary
                class="pl-[12px] pr-[12px]"
                wire:click="$emitTo('teacher.test-delete-modal', 'displayModal', '{{  $this->test->uuid }}')">
            <x-icon.trash/>
        </x-button.primary>
    @else
        <x-button.primary
                class="pl-[12px] pr-[12px] opacity-20 cursor-not-allowed">
            <x-icon.trash/>
        </x-button.primary>
    @endif
</div>
