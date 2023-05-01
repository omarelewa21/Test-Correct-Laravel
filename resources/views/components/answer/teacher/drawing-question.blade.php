<div class="w-full flex items-center justify-center border border-blue-grey rounded-10">
    <div class="relative w-full block drawing-question-img-container" @accordion-toggled.window="setHeightToAspectRatioAccordion($el, 940, 500)" @resize.window="setHeightToAspectRatio($el, 940, 500)">
        @if($studentAnswer && empty($answer->json))
            @lang('drawing-question.Geen afbeelding')
        @else
            <img src="{{ $imageSource }}"
                 class="block m-auto inset-0 absolute max-h-full"
                 alt="Drawing answer"
            >
            <div class="absolute bottom-4 right-4">
                <x-button.secondary wire:click="$emit('openModal', 'co-learning.drawing-question-preview-modal',
                                    {imgSrc: '{{ $imageSource }}', title: 'answer'})">
                    <x-icon.screen-expand />
                    <span>{{ __('co-learning.view_larger') }}</span>
                </x-button.secondary>
            </div>
        @endif
    </div>
</div>