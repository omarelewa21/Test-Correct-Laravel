<div @class(["test-action", $attributes->get('class')])
     {{ $attributes->except('class') }}
     x-data="testAction( () => Livewire.emit('openModal','teacher.test-delete-modal', {testUuid: @js($test->uuid)}) )"
>
    @if($variant == 'icon-button')
        @if (!$disabled)
            <x-button.primary
                    title="{{ __('teacher.Verwijderen') }}"
                    class="w-10 p-0 items-center justify-center"
                    x-on:click="handle()"
                    selid="test-delete-button"
            >
                <x-icon.trash />

            </x-button.primary>
        @else
            <x-button.primary
                    class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed"
                    selid="test-delete-button"
            >
                <x-icon.trash />
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                x-on:click="handle()"
                selid="test-delete-button"
        >
            <span class="w-5 flex justify-center"><x-icon.remove /></span>
            <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
        </button>
    @endif
</div>
