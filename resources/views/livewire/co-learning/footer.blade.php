<div class="flex">
    <div class="flex items-center">
        score placeholder
    </div>
    <div class="flex items-center">
        <span><b>{{ __('co-learning.answer') }} x</b>/x</span> {{--todo add counter and translation --}}
        <span><b>{{ __('co-learning.question') }} x</b>/x</span>
        @if($nextAnswerAvailable)
            <x-button.primary wire:click="updateAnswerRating()">
                Next Answer
                <x-icon.arrow class="ml-2"/>
            </x-button.primary>
        @endif
    </div>
</div>
