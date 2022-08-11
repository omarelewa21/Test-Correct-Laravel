<div {{ $attributes }}>
    @if($variant == 'icon-button')
        <x-button.primary class="pl-[12px] pr-[12px] "
                          @click="window.open('{!! $url !!}', '_blank')"
                          title="{{ __('teacher.Voorbeeld') }}"
        >
            <x-icon.preview/>
        </x-button.primary>
        <x-slot name="text">{{ __('teacher.Toets voorbeeldweergave') }}</x-slot>
    @elseif($variant == 'context-menu')
        <button
            class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));window.open('{!! $url !!}', '_blank')"
        >
            <span class="w-5 flex justify-center"><x-icon.preview/></span>
            <span class="text-base bold inherit">{{ __('cms.voorbeeld') }}</span>
        </button>
    @endif

</div>
