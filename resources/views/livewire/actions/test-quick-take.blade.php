<div class="test-action {{ $class ?? '' }}"
     x-data="testAction( () => $wire.call('handle') )"
>
    @if($variant == 'icon-button')
        @if($disabled)
            <x-button.primary class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed"
                              title="{{ __('cms.Direct afnemen') }}"
                              wire:key="qt-b-{{ $disabled ? 'abc' : 'def' }}"
            >
                <x-icon.schedule-now/>
            </x-button.primary>
        @else
            <x-button.primary class="w-10 p-0 items-center justify-center"
                              title="{{ __('cms.Direct afnemen') }}"
                              x-on:click="handle()"
                              wire:key="qt-b-{{ $disabled ? 'abc' : 'def' }}"
            >
                <x-icon.schedule-now/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                x-on:click="handle()"
        >
            <span class="w-5 flex justify-center"><x-icon.schedule-now/></span>
            <span class="text-base bold inherit">{{ __('cms.Direct afnemen') }}</span>
        </button>
    @endif
</div>
