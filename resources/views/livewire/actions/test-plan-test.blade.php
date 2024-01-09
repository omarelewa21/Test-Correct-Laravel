<div class="test-action {{ $class ?? '' }}"
     x-data="testAction( () => $wire.call('handle') )"
>
    @if($variant == 'icon-button-with-text')
        @if($disabled)
            <x-button.cta title="{{ __('teacher.Inplannen') }}"
                          class="px-4 opacity-20 cursor-not-allowed"
                          selid="test-plan-btn"
                          wire:key='test-plan-test-{{ $uuid }}'
            >
                <x-icon.schedule/>
                <span>{{ __('cms.Inplannen') }}</span>
            </x-button.cta>
        @else
            <x-button.cta x-on:click="handle()"
                          title="{{ __('teacher.Inplannen') }}"
                          class="px-4"
                          selid="test-plan-btn"
                          wire:key='test-plan-test-{{ $uuid }}'
            >
                <x-icon.schedule/>
                <span>{{ __('cms.Inplannen') }}</span>
            </x-button.cta>
        @endif
    @elseif($variant == 'icon-button')
        @if($disabled)
            <x-button.icon wire:key='test-plan-test-{{ $uuid }}' color="cta" class="opacity-20 cursor-not-allowed" title="{{ __('teacher.Inplannen') }}" selid="test-plan-btn">
                <x-icon.schedule/>
            </x-button.icon>
        @else
            <x-button.icon wire:key='test-plan-test-{{ $uuid }}' color="cta" x-on:click="handle()" title="{{ __('teacher.Inplannen') }}" selid="test-plan-btn">
                <x-icon.schedule/>
            </x-button.icon>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                x-on:click="handle()"
                selid="test-plan-btn"
                wire:key='test-plan-test-{{ $uuid }}'
        >
            <span class="w-5 flex justify-center"><x-icon.schedule/></span>
            <span class="text-base bold inherit">{{ __('cms.Inplannen') }}</span>
        </button>
    @endif
</div>
