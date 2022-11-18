<x-partials.co-learning-question-container :questionNumber="$questionNumber"
                                           :answerNumber="$answerNumber"
                                           :question="$question"
>
    <div class="w-full flex items-center justify-center {{--h-full--}}">
        <div class="relative w-fit {{--h-full max-h-full--}}">
            {{--            <x-input.group for="me" class="w-full disabled mt-4">--}}
            {{--                <div class="border border-light-grey p-4 rounded-10 h-fit">{!! $this->answer !!}</div>--}}
            {{--            </x-input.group>--}}
            @if($answered)
                <img src="{{$this->imgSrc}}" class="border border-blue-grey rounded-10 w-fit {{--object-contain h-full--}}">
                <div class="absolute bottom-4 right-4">
                    <x-button.secondary wire:click="$emit('openModal', 'co-learning.drawing-question-preview-modal', ['{{$this->answerRating->answer->getKey()}}'])">
                        <x-icon.screen-expand/>
                        <span>{{ __('co-learning.view_larger') }}</span>
                    </x-button.secondary>
                </div>
            @endif

        </div>
    </div>
</x-partials.co-learning-question-container>
