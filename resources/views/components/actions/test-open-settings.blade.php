<div {{ $attributes }}>
    @if($variant == 'icon-button')
        @if($test->canEdit(auth()->user()))
            <x-button.primary class="w-10 p-0 items-center justify-center"
                              title="{{ __('teacher.Instellingen') }}"
                              wire:click="$emit('openModal', 'teacher.test-edit-modal', {{ json_encode(['testUuid' => $test->uuid ]) }})">
                <x-icon.settings/>
            </x-button.primary>
        @else
            <x-button.primary class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed">
                <x-icon.settings/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if( $test->canEdit(auth()->user()))
            <button
                    class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));"
                    wire:click="$emit('openModal', 'teacher.test-edit-modal', {{ json_encode(['testUuid' => $test->uuid ]) }})"
            >
                <span class="w-5 flex justify-center"><x-icon.settings/></span>
                <span class="text-base bold inherit">{{ __('cms.Instellingen') }}</span>
            </button>
        @endif
    @endif
</div>
