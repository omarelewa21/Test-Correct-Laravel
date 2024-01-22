<div class="test-action {{ $class ?? '' }}"
     x-data="testAction( () => $wire.call('handle') )"
>
    @if($variant == 'icon-button-with-text')
        @if($disabled)
            <x-button.cta title="{{ __('test.publish') }}"
                          class="px-4 opacity-20 cursor-not-allowed"
            >
                <x-icon.publish/>
                <span>{{ __('test.publish') }}</span>
            </x-button.cta>
        @else
            <x-button.cta x-on:click="handle()"
                          title="{{ __('test.publish') }}"
                          class="px-4"
                          selid="publish-test-btn"
            >
                <x-icon.publish/>
                <span>{{ __('test.publish') }}</span>
            </x-button.cta>
        @endif
    @elseif($variant == 'icon-button')
        @if($disabled)
            <x-button.icon color="cta" class="opacity-20 cursor-not-allowed" title="{{ __('test.publish') }}">
                <x-icon.publish/>
            </x-button.icon>
        @else
            <x-button.icon color="cta" x-on:click="handle()" title="{{ __('test.publish') }}" selid="publish-test-btn">
                <x-icon.publish/>
            </x-button.icon>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                x-on:click="handle()"
                selid="publish-test-btn"
        >
            <span class="w-5 flex justify-center"><x-icon.publish/></span>
            <span class="text-base bold inherit">{{ __('test.publish') }}</span>
        </button>
    @endif
</div>
