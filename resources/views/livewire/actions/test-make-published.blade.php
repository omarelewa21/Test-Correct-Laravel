<div class="{{ $class ?? '' }}">
    @if($variant == 'icon-button-with-text')
        @if($disabled)
            <x-button.cta title="{{ __('test.publish') }}"
                          class="px-4 opacity-20 cursor-not-allowed"
            >
                <x-icon.preview/>
                <span>{{ __('test.publish') }}</span>
            </x-button.cta>
        @else
            <x-button.cta wire:click="handle"
                          title="{{ __('test.publish') }}"
                          class="px-4"
            >
                <x-icon.preview/>
                <span>{{ __('test.publish') }}</span>
            </x-button.cta>
        @endif
    @elseif($variant == 'icon-button')
        @if($disabled)
            <x-button.icon color="cta opacity-20 cursor-not-allowed" title="{{ __('test.publish') }}">
                <x-icon.preview/>
            </x-button.icon>
        @else
            <x-button.icon color="cta" wire:click="handle" title="{{ __('test.publish') }}">
                <x-icon.preview/>
            </x-button.icon>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                wire:click="handle"
        >
            <span class="w-5 flex justify-center"><x-icon.preview/></span>
            <span class="text-base bold inherit">{{ __('test.publish') }}</span>
        </button>
    @endif
</div>
