<div class="{{ $class ?? '' }}"
     x-data="{
         makePDF: function() {
                        $wire.emit('openModal', 'teacher.pdf-download-modal', {test: '{{$uuid}}'});
                    }
        }"
>
    @if($variant == 'icon-button')
        @if($disabled)
            <x-button.primary
                    class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed"
                    title="{{ __('teacher.PDF maken') }}"
            >
                <x-icon.pdf-file color="var(--off-white)"/>
            </x-button.primary>
        @else
            <x-button.primary
                    class="w-10 p-0 items-center justify-center"
                    x-on:click="makePDF()"
                    title="{{ __('teacher.PDF maken') }}"
            >
                <x-icon.pdf-file color="var(--off-white)"/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu' && !$disabled)
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                @click="makePDF()"
        >
            <span class="w-5 flex justify-center"><x-icon.pdf-file/></span>
            <span class="text-base bold inherit">{{ __('cms.PDF maken') }}</span>
        </button>
    @endif
</div>
