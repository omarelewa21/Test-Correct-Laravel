<div id="co-learning-preview" class="flex flex-1 flex-col h-full"

>
    <div class="co-learning-drawing-modal-header">
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

        <div class="flex ml-auto items-center space-x-2.5">
            <x-button.close wire:click="$emit('closeModal')"/>
        </div>
    </div>

    <div class="preview-content mx-auto bg-lightGrey {{-- before-shadow --}}relative"
         wire:ignore.self
    >
        {{ $slot }}
    </div>
</div>

