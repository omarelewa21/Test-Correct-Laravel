<div class="w-full flex items-center justify-center">
    <div class="relative w-full">
        <img src="{{ $imageSource }}"
             class="border border-blue-grey rounded-10 w-full"
             alt="Drawing answer"
        >
        <div class="absolute bottom-4 right-4">
            <x-button.secondary wire:click="$emit('openModal', 'co-learning.drawing-question-preview-modal',
                                    {imgSrc: '{{ $imageSource }}', title: 'answer'})">
                <x-icon.screen-expand />
                <span>{{ __('co-learning.view_larger') }}</span>
            </x-button.secondary>
        </div>
    </div>
</div>