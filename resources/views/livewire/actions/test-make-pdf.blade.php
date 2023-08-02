<div class="{{ $class ?? '' }}"
     x-data="{
         makePDF: () => $wire.emit('openModal', 'teacher.pdf-download-modal', {uuid: @js($this->uuid) })
        }"
     title="{{ __('teacher.Exporteren naar PDF') }}"
>
    @if($variant == 'icon-button')
        @if($disabled)
            <x-button.primary
                    class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed off-white"
            >
                <x-icon.pdf-file/>
            </x-button.primary>
        @else
            <x-button.primary
                    class="w-10 p-0 items-center justify-center off-white"
                    x-on:click="makePDF()"
            >
                <x-icon.pdf-file/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                @click="makePDF()"
        >
            <span class="w-5 flex justify-center"><x-icon.pdf-file/></span>
            <span class="text-base bold inherit">{{ __('cms.Exporteren naar PDF') }}</span>
        </button>
    @endif
</div>
