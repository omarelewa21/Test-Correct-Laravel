<div x-data="{
        showButton:true,
        base: true,
        open: false,
        url: false,
        addLink() {
                if (this.$refs.videolink.value.length > 0) {
                    $wire.emit('new-video-attachment', this.$refs.videolink.value);
                    this.$refs.videolink.value = '';
                }
                this.url = false;
                this.open = true;
            }
        }"
     x-show="showButton"
     class="flex rounded-10 items-center transition ease-out duration-500"
     :class="open || url ? 'border-bluegrey border' : '' "
     style="max-width: 600px;"
     @click.outside="base = true; open = false; url = false;"
     @filepond-start.window="showButton = false;"
     @filepond-finished.window="showButton = true;"
>
    <div>
        <div x-show="base"
             x-cloak
             class="flex py-px transition duration-500 items-center flex-wrap">
            @if($disabled)
            <x-button.secondary disabled class="max-h-10 min-w-max disabled">
                {{ $text }}
            </x-button.secondary>
            @else
            <x-button.secondary @click="base = false; open = true" class="max-h-10 min-w-max">
                {{ $text }}
            </x-button.secondary>
            @endif
            <span class="flex italic text-base mx-4 min-w-max">
                {!!__('cms.Of sleep je bijlage over dit vak')  !!}
            </span>
        </div>
        <button x-show="open"
                x-cloak
                class="px-5 space-x-2 items-center inline-flex max-h-10 min-w-max transition duration-500"
                @click="base = true; open = false"
        >
            {{ $text }}
        </button>
    </div>
    <div class="relative overflow-hidden transition duration-500 flex"
         :style="open ? 'max-width: 100%' : 'opacity: 0; height: 0; max-width: 0'"
         x-show="open"
         x-cloak
    >
        <div class="flex space-x-2">
            <x-button.secondary class="px-3" @click="document.querySelector('.filepond--label-action').click();base = false; open = true">
                <x-icon.upload/>
                <span selid="upload-btn">{{ __('cms.Upload') }}</span>
            </x-button.secondary>
            <x-button.secondary class="px-3" @click="open = false; url = true">
                <x-icon.link/>
                <span selid="video-link-btn">{{ __('cms.Video URL') }}</span>
            </x-button.secondary>
        </div>
    </div>

    <div class="relative overflow-hidden transition duration-500 flex"
         :style="url ? 'max-width: 100%' : 'opacity: 0; height: 0; max-width: 0'"
         x-show="url"
         x-transition:delay.200ms=""
         x-cloak
    >
        <button x-show="url"
                x-cloak
                class="px-5 space-x-2 items-center inline-flex max-h-10 min-w-max transition duration-500"
                @click="url = false; open = true"
        >
            <x-icon.link/>
            <span class="bold">{{ __('cms.Video URL') }}</span>
        </button>
        <div class="flex relative" wire:ignore>
            <x-input.text x-ref="videolink" class="w-60 pr-12 text-base" placeholder="link" @keyup.enter="addLink()" selid="video-link-input"/>
            <x-button.cta class="px-3 absolute -right-px" @click="addLink()" selid="video-link-input-confirm">
                <x-icon.checkmark/>
            </x-button.cta>
        </div>
    </div>
</div>