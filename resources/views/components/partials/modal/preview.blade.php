<div id="preview-modal" class="flex flex-1 flex-col h-full"

>
    <div class="preview-modal-header">
        <div class="bold flex items-center min-w-max space-x-2.5 text-lg">
            @isset($icon)
                {{ $icon }}
            @else
                <x-icon.attachment/>
            @endif
        </div>

        <span class="line-clamp-1 break-all px-2.5 header-title">
            {{ $this->title ?? $title ?? '' }}
        </span>

        <div class="flex ml-auto items-center space-x-2.5 device-dependent-margin">
            <x-button.close wire:click="$emit('closeModal')" class="bg-white"/>
        </div>
    </div>

    <div class="preview-modal-content mx-auto bg-lightGrey relative"
         wire:ignore.self
    >
        {{ $slot }}
    </div>
</div>

