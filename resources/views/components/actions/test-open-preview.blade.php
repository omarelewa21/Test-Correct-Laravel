<div @class(["test-action", $attributes->get('class')])
     {{ $attributes->except('class') }}
     x-data="testAction( () => window.open('{!! $url !!}', '_blank') )"
>
    @if($variant == 'icon-button')
        @if(!$disabled)
            <x-button.primary class="w-10 p-0 items-center justify-center"
                              x-on:click="handle()"
                              title="{{ __('teacher.Voorbeeld') }}"
            >
                <x-icon.preview />
            </x-button.primary>
        @else
            <x-button.primary class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed"
                              title="{{ __('teacher.Voorbeeld') }}"
            >
                <x-icon.preview />
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if(!$disabled)
            <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    x-on:click="handle()"
            >
                <span class="w-5 flex justify-center"><x-icon.preview /></span>
                <span class="text-base bold inherit">{{ __('cms.voorbeeld') }}</span>
            </button>
        @endif
    @endif

</div>
