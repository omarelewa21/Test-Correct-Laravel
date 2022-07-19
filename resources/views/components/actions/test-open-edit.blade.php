<div>
    @if($variant == 'icon-button')
        @if($this->test->canEdit(auth()->user()))
            <x-tooltip-as-a-wrapper>
            <x-button.primary class="pl-[12px] pr-[12px]"
                              @click="window.open('{!! $url !!}', '_self')">
                <x-icon.edit/>
            </x-button.primary>
                <x-slot name="text">{{ __('cms.Wijzigen') }}</x-slot>
            </x-tooltip-as-a-wrapper>
        @else
            <x-button.primary class="pl-[12px] pr-[12px] opacity-20 cursor-not-allowed">
                <x-icon.edit/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if( $test->canEdit(auth()->user()))
            <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    wire:click="openEdit('{{ $test->uuid }}')"
                    @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));"
            >
                <x-icon.edit/>
                <span class="text-base bold inherit">{{ __('cms.Construeren') }}</span>
            </button>
        @endif
    @endif
</div>
