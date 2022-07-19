<div>
    @if ($variant == 'icon-button')
        <x-tooltip-as-a-wrapper>
            <x-button.primary
                class="pl-[12px] pr-[12px]"
                wire:click="duplicateTest"
            >
                <x-icon.copy/>
            </x-button.primary>
            <x-slot name="text">
                                <span class="text-base text-left">
                                    {{ __('cms.Kopie maken') }}
                                </span>
            </x-slot>
        </x-tooltip-as-a-wrapper>
    @elseif($variant == 'context-menu')
        <button
            class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            wire:click="duplicateTest"
        >
            <x-icon.copy/>
            <span class="text-base bold inherit">{{ __('cms.Kopie maken') }}</span>
        </button>
    @endif
</div>
