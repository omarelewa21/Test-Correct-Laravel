<div class="w-full flex items-center justify-center">
    <div class="relative w-full">
        @if($studentAnswer && empty($answer->json))
            @lang('drawing-question.Geen afbeelding')
        @else
            <div class="border border-blue-grey rounded-10 w-full"
                 x-data="{loaded: false, error: false}"
                 x-bind:class="{'h-40': !loaded}"
            >
                <img src="{{ $imageSource }}"
                     alt="Drawing answer"
                     x-on:load="$nextTick(() => loaded = true)"
                     x-on:error="$nextTick(() => error = true)"
                     x-show="loaded"
                >
                <div class="absolute bottom-4 right-4" x-show="loaded">
                    <x-button.secondary wire:click="$emit('openModal', 'co-learning.drawing-question-preview-modal',
                                    {imgSrc: '{{ $imageSource }}', title: 'answer'})">
                        <x-icon.screen-expand />
                        <span>{{ __('co-learning.view_larger') }}</span>
                    </x-button.secondary>
                </div>
                <div class="absolute inset-0 flex items-center justify-center"
                     x-show="!loaded"
                >
                    <span x-show="error">@lang('general.image unavailable')</span>
                    <span x-show="!error">@lang('general.loading')</span>
                </div>
            </div>
        @endif
    </div>
</div>