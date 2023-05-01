<x-partials.co-learning-question-container :question="$question"
>
    <div class="w-full flex items-center justify-center border border-blue-grey rounded-10">
        <div class="relative w-full block " x-init="setHeightToAspectRatio($el, 940, 500)">
            @if($answered)
                <img src="{{ $imgSrc }}"
                     class="block m-auto inset-0 absolute max-h-full"
                     alt="Drawing answer"
                     {{--style="width: {{ $this->imageWidth() }}; height: {{ $this->imageHeight() }}"--}}
                >
                <div class="absolute bottom-4 right-4">
                    <x-button.secondary wire:click="$emit('openModal', 'co-learning.drawing-question-preview-modal', {imgSrc: '{{ $imgSrc }}' })">
                        <x-icon.screen-expand/>
                        <span>{{ __('co-learning.view_larger') }}</span>
                    </x-button.secondary>
                </div>
            @endif
        </div>
    </div>
</x-partials.co-learning-question-container>
