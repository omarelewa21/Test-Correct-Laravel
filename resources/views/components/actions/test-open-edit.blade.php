<div {{ $attributes }}>
    @if($variant == 'icon-button')
        @if($this->test->canEdit(auth()->user()))
            <x-button.primary class="pl-[12px] pr-[12px]"
                              title="{{ __('teacher.Construeren') }}"
                              @click="window.open('{!! $url !!}', '_self')">
                <x-icon.edit/>
            </x-button.primary>
            <x-slot name="text">{{ __('cms.Wijzigen') }}</x-slot>
        @else
            <x-button.primary class="pl-[12px] pr-[12px] opacity-20 cursor-not-allowed">
                <x-icon.edit/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if( $test->canEdit(auth()->user()))
            <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                @click="window.open('{!! $url !!}', '_self')"
                @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));"
            >
                <span class="w-5 flex justify-center"><x-icon.edit/></span>
                <span class="text-base bold inherit">{{ __('cms.Construeren') }}</span>
            </button>
        @endif
    @endif
</div>
