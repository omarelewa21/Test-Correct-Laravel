<div class="{{ $class ?? '' }}">
        @if ($variant == 'icon-button')
            @if($disabled)
                <x-button.primary
                        class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed"
                        title="{{ __('teacher.Kopie maken') }}"
                >
                    <x-icon.copy/>
                </x-button.primary>
            @else
            <x-button.primary
                    class="w-10 p-0 items-center justify-center"
                    wire:click="duplicateTest"
                    title="{{ __('teacher.Kopie maken') }}"
            >
                <x-icon.copy/>
            </x-button.primary>
            @endif
        @elseif($variant == 'context-menu' && !$disabled)
            <button
                    class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    wire:click="duplicateTest"
            >
                <span class="w-5 flex justify-center"><x-icon.copy/></span>
                <span class="text-base bold inherit">{{ __('cms.Kopie maken') }}</span>
            </button>
        @endif
</div>
