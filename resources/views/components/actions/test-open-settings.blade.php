<div @class(["test-action", $attributes->get('class')])
     {{ $attributes->except('class') }}
     x-data="testAction( () => Livewire.emit('openModal', @js($modalName), {testUuid: @js($test->uuid)}) )"
>
    @if($variant == 'icon-button')
        @if(!$disabled)
            <x-button.primary class="w-10 p-0 items-center justify-center"
                              title="{{ __('teacher.Instellingen') }}"
                              x-on:click="handle()">
                <x-icon.settings />
            </x-button.primary>
        @else
            <x-button.primary class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed">
                <x-icon.settings />
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if(!$disabled)
            <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    x-on:click="handle()"
            >
                <span class="w-5 flex justify-center"><x-icon.settings /></span>
                <span class="text-base bold inherit">{{ __('cms.Instellingen') }}</span>
            </button>
        @endif
    @endif
</div>
